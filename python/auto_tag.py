"""
auto_tag.py

This script classifies legal case content into legal categories and generates tags.
It uses a simple keyword-based approach for demonstration, but can be extended to use ML models.

Requirements:
- argparse
- json

Usage:
    python auto_tag.py --input_file path/to/case.txt
    OR
    python auto_tag.py --text "Full case text here"

Returns JSON with keys: categories, tags
"""

import argparse
import json
import sys
import re

# Example legal categories and associated keywords
CATEGORY_KEYWORDS = {
    'Contract Law': [r'contract', r'agreement', r'breach', r'consideration', r'offer', r'acceptance'],
    'Tort Law': [r'negligence', r'duty', r'liability', r'damages', r'tort', r'personal injury'],
    'Criminal Law': [r'crime', r'criminal', r'prosecution', r'guilt', r'sentence', r'felony', r'misdemeanor'],
    'Property Law': [r'property', r'ownership', r'title', r'land', r'real estate', r'easement'],
    'Constitutional Law': [r'constitution', r'amendment', r'due process', r'equal protection', r'rights', r'supreme court'],
    'Family Law': [r'marriage', r'divorce', r'custody', r'child support', r'alimony'],
    'Administrative Law': [r'agency', r'regulation', r'administrative', r'rulemaking'],
    'Intellectual Property': [r'copyright', r'patent', r'trademark', r'infringement'],
    'International Law': [r'treaty', r'international', r'foreign', r'sovereignty'],
}

# Flatten all keywords for tag extraction
ALL_KEYWORDS = set(kw for kws in CATEGORY_KEYWORDS.values() for kw in kws)

def classify(text):
    """Classify text into legal categories and extract tags."""
    text_lower = text.lower()
    categories = []
    tags = set()
    for category, keywords in CATEGORY_KEYWORDS.items():
        for kw in keywords:
            if re.search(rf'\\b{kw}\\b', text_lower):
                categories.append(category)
                tags.add(kw)
                break  # Only need one keyword to match category
    # Extract all tags
    for kw in ALL_KEYWORDS:
        if re.search(rf'\\b{kw}\\b', text_lower):
            tags.add(kw)
    if not categories:
        categories = ['Uncategorized']
    return {'categories': categories, 'tags': sorted(tags)}

def main():
    parser = argparse.ArgumentParser(description='Classify legal text and generate tags.')
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument('--input_file', type=str, help='Path to file containing legal case text')
    group.add_argument('--text', type=str, help='Raw legal case text as input')
    args = parser.parse_args()

    if args.input_file:
        with open(args.input_file, 'r', encoding='utf-8') as f:
            text = f.read()
    else:
        text = args.text

    if not text or len(text.strip()) < 30:
        print(json.dumps({'status': 'error', 'message': 'Input text too short or missing.'}))
        sys.exit(1)

    result = classify(text)
    print(json.dumps({'status': 'ok', **result}, ensure_ascii=False, indent=2))

if __name__ == '__main__':
    main()
