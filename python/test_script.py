#!/usr/bin/env python3
import json
import sys
from pathlib import Path

# Test basic functionality
print("Testing LexiAid Python components...")

# Test 1: JSON file loading
try:
    json_path = Path(__file__).parent / 'legal_documents.json'
    with open(json_path, 'r', encoding='utf-8') as f:
        data = json.load(f)
    documents = data.get('documents', [])
    print(f"✓ JSON file loaded successfully: {len(documents)} documents")
except Exception as e:
    print(f"✗ JSON file loading failed: {e}")
    sys.exit(1)

# Test 2: SentenceTransformers import
try:
    from sentence_transformers import SentenceTransformer
    print("✓ SentenceTransformers imported successfully")
except Exception as e:
    print(f"✗ SentenceTransformers import failed: {e}")
    sys.exit(1)

# Test 3: Model loading (this might take time on first run)
try:
    print("Loading model (this may take a moment on first run)...")
    model = SentenceTransformer('all-MiniLM-L6-v2')
    print("✓ SentenceTransformers model loaded successfully")
except Exception as e:
    print(f"✗ Model loading failed: {e}")
    sys.exit(1)

# Test 4: Simple embedding test
try:
    test_text = "constitutional law"
    embedding = model.encode(test_text)
    print(f"✓ Text encoding successful: {len(embedding)} dimensions")
except Exception as e:
    print(f"✗ Text encoding failed: {e}")
    sys.exit(1)

print("All tests passed! Python components are working correctly.")
