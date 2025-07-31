#!/usr/bin/env python3
"""
LexiAid Semantic Search Engine
Uses sentence-transformers for semantic similarity search on legal documents.
"""

import argparse
import json
import sys
from pathlib import Path
import numpy as np
from sentence_transformers import SentenceTransformer
from typing import List, Dict
import mysql.connector
from mysql.connector import Error

class LegalSearchEngine:
    def __init__(self, model_name: str = 'all-MiniLM-L6-v2'):
        """Initialize the search engine with the specified transformer model."""
        self.model = SentenceTransformer(model_name)
        self.documents = []
        self.embeddings = None
        self.load_documents()

    def load_documents(self) -> None:
        """
        Load legal documents from MySQL database.
        Falls back to local JSON if DB connection fails.
        """
        try:
            # Try loading from MySQL first
            self.documents = self._load_from_database()
        except Exception as e:
            print(f"Database loading failed: {e}", file=sys.stderr)
            # Fallback to local JSON file
            json_path = Path(__file__).parent / 'legal_documents.json'
            if json_path.exists():
                with open(json_path, 'r', encoding='utf-8') as f:
                    self.documents = json.load(f)
            else:
                print("No documents available", file=sys.stderr)
                self.documents = []

        # Pre-compute embeddings for all documents
        if self.documents:
            texts = [doc['content'] for doc in self.documents]
            self.embeddings = self.model.encode(texts, convert_to_tensor=True)

    def _load_from_database(self) -> List[Dict]:
        """Load documents from MySQL database."""
        config = {
            'host': 'localhost',
            'user': 'root',
            'password': '',
            'database': 'lexiaid_db'
        }

        try:
            conn = mysql.connector.connect(**config)
            cursor = conn.cursor(dictionary=True)
            
            cursor.execute("""
                SELECT resource_id, title, content, type, jurisdiction, tags 
                FROM legal_resources 
                WHERE content IS NOT NULL
            """)
            
            documents = cursor.fetchall()
            return documents

        except Error as e:
            print(f"Database error: {e}", file=sys.stderr)
            raise
        finally:
            if 'conn' in locals() and conn.is_connected():
                cursor.close()
                conn.close()

    def search(self, query: str, top_k: int = 5) -> List[Dict]:
        """
        Perform semantic search on the documents.
        Returns top_k most similar documents with their scores.
        """
        if not self.documents or self.embeddings is None:
            return []

        # Encode the query
        query_embedding = self.model.encode(query, convert_to_tensor=True)

        # Calculate cosine similarity
        similarities = np.dot(self.embeddings, query_embedding) / (
            np.linalg.norm(self.embeddings, axis=1) * np.linalg.norm(query_embedding)
        )

        # Get top_k results
        top_indices = np.argsort(similarities)[-top_k:][::-1]
        
        results = []
        for idx in top_indices:
            doc = self.documents[idx]
            result = {
                'title': doc['title'],
                'summary': doc['content'][:200] + '...' if len(doc['content']) > 200 else doc['content'],
                'similarity_score': float(similarities[idx]),
                'tags': doc.get('tags', []),
                'resource_id': doc.get('resource_id'),
                'jurisdiction': doc.get('jurisdiction'),
                'type': doc.get('type')
            }
            results.append(result)

        return results

def main():
    parser = argparse.ArgumentParser(description='Legal document semantic search')
    parser.add_argument('query', type=str, help='The search query')
    parser.add_argument('--top_k', type=int, default=5, help='Number of results to return')
    args = parser.parse_args()

    search_engine = LegalSearchEngine()
    results = search_engine.search(args.query, args.top_k)
    
    # Output JSON results
    print(json.dumps({'results': results}, ensure_ascii=False))

if __name__ == '__main__':
    main()
