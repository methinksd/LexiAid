import sys
import json
import argparse
import os
import numpy as np
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity
import logging
from pathlib import Path

# Set up logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(Path(__file__).parent / 'search.log'),
        logging.StreamHandler()
    ]
)

class LegalSearchEngine:
    def __init__(self, model_name='all-MiniLM-L6-v2'):
        """Initialize the search engine with the specified transformer model."""
        try:
            self.model = SentenceTransformer(model_name)
            self.documents = []
            self.embeddings = None
            self.load_documents()
        except Exception as e:
            logging.error(f"Failed to initialize search engine: {e}")
            raise

    def load_documents(self):
        """Load legal documents from JSON file."""
        try:
            json_path = Path(__file__).parent / 'legal_documents.json'
            if not json_path.exists():
                raise FileNotFoundError(f"Documents file not found at {json_path}")
            
            with open(json_path, 'r', encoding='utf-8') as f:
                data = json.load(f)
                self.documents = data.get('documents', [])
                
            if not self.documents:
                logging.warning("No documents loaded from JSON file")
                return
            
            # Pre-compute embeddings for all documents
            texts = [f"{doc['title']}. {doc['content']}" for doc in self.documents]
            self.embeddings = self.model.encode(texts)
            logging.info(f"Loaded {len(self.documents)} documents and computed embeddings")
            
        except json.JSONDecodeError:
            logging.error("Invalid JSON in documents file")
            raise
        except Exception as e:
            logging.error(f"Error loading documents: {e}")
            raise

    def search(self, query, top_k=5, min_score=0.3):
        """
        Perform semantic search on the documents.
        Args:
            query (str): The search query
            top_k (int): Number of results to return
            min_score (float): Minimum similarity score threshold
        Returns:
            list: Top matching documents with their scores
        """
        try:
            if not self.documents or self.embeddings is None:
                return []

            # Encode the query
            query_embedding = self.model.encode(query)
            
            # Calculate cosine similarity
            similarities = cosine_similarity([query_embedding], self.embeddings)[0]
            
            # Get top_k results above threshold
            top_indices = np.argsort(similarities)[::-1]
            
            results = []
            for idx in top_indices:
                score = float(similarities[idx])
                if score < min_score:
                    break
                    
                doc = self.documents[idx]
                results.append({
                    'title': doc['title'],
                    'summary': doc['content'][:200] + '...' if len(doc['content']) > 200 else doc['content'],
                    'similarity_score': score,
                    'tags': doc.get('tags', []),
                    'year': doc.get('year', 'N/A')
                })
                
                if len(results) >= top_k:
                    break
            
            return results
            
        except Exception as e:
            logging.error(f"Search error: {e}")
            raise

def main():
    parser = argparse.ArgumentParser(description='Legal document semantic search')
    parser.add_argument('query', help='The search query')
    parser.add_argument('--top_k', type=int, default=5, help='Number of results to return')
    parser.add_argument('--min-score', type=float, default=0.3, help='Minimum similarity score threshold')
    args = parser.parse_args()
    
    try:
        search_engine = LegalSearchEngine()
        results = search_engine.search(args.query, args.top_k, args.min_score)
        
        response = {
            'status': 'success',
            'results': results,
            'query': args.query,
            'count': len(results)
        }
        print(json.dumps(response, ensure_ascii=False))
        return 0
        
    except Exception as e:
        error_response = {
            'status': 'error',
            'message': str(e)
        }
        print(json.dumps(error_response, ensure_ascii=False), file=sys.stderr)
        return 1

if __name__ == '__main__':
    sys.exit(main())
