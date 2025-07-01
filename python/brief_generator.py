"""
brief_generator.py

This script takes the full text of a legal case and generates a structured case brief using a transformer-based summarization model (T5 or BART).

Requirements:
- transformers
- torch
- argparse
- json

Usage:
    python brief_generator.py --input_file path/to/case.txt
    OR
    python brief_generator.py --text "Full case text here"

Returns JSON with keys: facts, issues, holding, reasoning, principles
"""

import argparse
import json
import sys
from transformers import pipeline, AutoTokenizer, AutoModelForSeq2SeqLM

# You can swap model_name for 'facebook/bart-large-cnn' or another summarization model
MODEL_NAME = 't5-base'

# Prompts for each section of the brief
BRIEF_SECTIONS = {
    'facts': 'Summarize the facts of the following legal case:',
    'issues': 'List the legal issues in the following case:',
    'holding': 'State the holding or decision in the following case:',
    'reasoning': 'Explain the legal reasoning in the following case:',
    'principles': 'Identify the key legal principles in the following case:'
}

def load_model():
    """Load the summarization pipeline."""
    tokenizer = AutoTokenizer.from_pretrained(MODEL_NAME)
    model = AutoModelForSeq2SeqLM.from_pretrained(MODEL_NAME)
    summarizer = pipeline('summarization', model=model, tokenizer=tokenizer)
    return summarizer

def generate_brief(text, summarizer):
    """Generate a structured case brief from text."""
    brief = {}
    for section, prompt in BRIEF_SECTIONS.items():
        input_text = f"{prompt}\n{text}"
        # T5 expects a prefix for summarization
        if MODEL_NAME.startswith('t5'):
            input_text = f"summarize: {input_text}"
        summary = summarizer(input_text, max_length=120, min_length=20, do_sample=False)[0]['summary_text']
        brief[section] = summary.strip()
    return brief

def main():
    parser = argparse.ArgumentParser(description='Generate a structured case brief from legal text.')
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

    summarizer = load_model()
    brief = generate_brief(text, summarizer)
    print(json.dumps({'status': 'ok', 'brief': brief}, ensure_ascii=False, indent=2))

if __name__ == '__main__':
    main()
