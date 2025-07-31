#!/usr/bin/env python3
"""
Debug version of semantic_search.py to identify issues
"""
import json
import sys
import argparse
from pathlib import Path

print("Debug: Starting semantic search debug script", file=sys.stderr)

# Test JSON loading
try:
    json_path = Path(__file__).parent / 'legal_documents.json'
    print(f"Debug: Loading JSON from {json_path}", file=sys.stderr)
    with open(json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)
    documents = data.get('documents', [])
    print(f"Debug: Loaded {len(documents)} documents", file=sys.stderr)
except Exception as e:
    print(f"Debug: JSON loading error: {e}", file=sys.stderr)
    sys.exit(1)

# Test imports
try:
    print("Debug: Importing SentenceTransformer", file=sys.stderr)
    from sentence_transformers import SentenceTransformer
    print("Debug: Import successful", file=sys.stderr)
except Exception as e:
    print(f"Debug: Import error: {e}", file=sys.stderr)
    sys.exit(1)

# Test model loading
try:
    print("Debug: Loading model", file=sys.stderr)
    model = SentenceTransformer('all-MiniLM-L6-v2')
    print("Debug: Model loaded", file=sys.stderr)
except Exception as e:
    print(f"Debug: Model loading error: {e}", file=sys.stderr)
    sys.exit(1)

# Test encoding
try:
    print("Debug: Testing encoding", file=sys.stderr)
    texts = [f"{doc['title']}. {doc['content']}" for doc in documents]
    embeddings = model.encode(texts[:2])  # Just encode first 2 for testing
    print(f"Debug: Encoded {len(embeddings)} documents", file=sys.stderr)
except Exception as e:
    print(f"Debug: Encoding error: {e}", file=sys.stderr)
    sys.exit(1)

# Simple successful response
response = {
    'status': 'success',
    'results': [
        {
            'title': documents[0]['title'],
            'summary': documents[0]['content'][:100] + '...',
            'similarity_score': 0.95,
            'tags': documents[0].get('tags', []),
            'year': documents[0].get('year', 'N/A')
        }
    ],
    'query': 'test query',
    'count': 1
}

print(json.dumps(response, ensure_ascii=False))
