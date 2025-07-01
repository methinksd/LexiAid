#!/usr/bin/env python3
"""
Simplified semantic search with fallback to keyword matching
"""
import json
import sys
import argparse
from pathlib import Path
import re

def simple_keyword_search(query, documents, top_k=5):
    """Fallback keyword-based search when ML models are unavailable"""
    query_words = set(query.lower().split())
    results = []
    
    for doc in documents:
        # Create searchable text
        searchable = f"{doc['title']} {doc['content']} {' '.join(doc.get('tags', []))}".lower()
        
        # Count keyword matches
        matches = sum(1 for word in query_words if word in searchable)
        
        if matches > 0:
            # Simple relevance score based on keyword matches and length
            score = matches / len(query_words)
            
            results.append({
                'title': doc['title'],
                'summary': doc['content'][:200] + '...' if len(doc['content']) > 200 else doc['content'],
                'similarity_score': score,
                'tags': doc.get('tags', []),
                'year': doc.get('year', 'N/A')
            })
    
    # Sort by score and return top results
    results.sort(key=lambda x: x['similarity_score'], reverse=True)
    return results[:top_k]

def semantic_search_with_fallback(query, documents, top_k=5, min_score=0.3):
    """Try semantic search, fallback to keyword search if needed"""
    try:
        # Try to import and use SentenceTransformers
        from sentence_transformers import SentenceTransformer
        import numpy as np
        from sklearn.metrics.pairwise import cosine_similarity
        
        # Load model (with timeout protection)
        model = SentenceTransformer('all-MiniLM-L6-v2')
        
        # Encode documents
        texts = [f"{doc['title']}. {doc['content']}" for doc in documents]
        doc_embeddings = model.encode(texts)
        
        # Encode query
        query_embedding = model.encode(query)
        
        # Calculate similarities
        similarities = cosine_similarity([query_embedding], doc_embeddings)[0]
        
        # Get top results
        top_indices = np.argsort(similarities)[::-1]
        
        results = []
        for idx in top_indices:
            score = float(similarities[idx])
            if score < min_score:
                break
                
            doc = documents[idx]
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
        # Fallback to keyword search
        print(f"Using keyword search fallback: {e}", file=sys.stderr)
        return simple_keyword_search(query, documents, top_k)

def main():
    parser = argparse.ArgumentParser(description='Legal document search with fallback')
    parser.add_argument('query', help='The search query')
    parser.add_argument('--top_k', type=int, default=5, help='Number of results to return')
    parser.add_argument('--min-score', type=float, default=0.3, help='Minimum similarity score threshold')
    args = parser.parse_args()
    
    try:
        # Load documents
        json_path = Path(__file__).parent / 'legal_documents.json'
        with open(json_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
        documents = data.get('documents', [])
        
        if not documents:
            raise ValueError("No documents found in JSON file")
        
        # Perform search
        results = semantic_search_with_fallback(args.query, documents, args.top_k, args.min_score)
        
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
