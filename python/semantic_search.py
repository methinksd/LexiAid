#!/usr/bin/env python3
"""
LexiAid Semantic Search Engine
AI-powered legal document search using transformer models
"""

import sys
import json
import argparse
import os
import numpy as np
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity
import logging
from pathlib import Path

# Set up logging to file only (not to stdout to avoid interfering with JSON output)
log_file = Path(__file__).parent / 'search.log'
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(log_file, mode='a')
    ]
)

class LegalSearchEngine:
    def __init__(self, model_name='all-MiniLM-L6-v2'):
        """Initialize the search engine with the specified transformer model."""
        try:
            logging.info(f"Initializing LegalSearchEngine with model: {model_name}")
            self.model = SentenceTransformer(model_name)
            self.documents = []
            self.embeddings = None
            self.load_documents()
            logging.info("LegalSearchEngine initialized successfully")
        except Exception as e:
            logging.error(f"Failed to initialize search engine: {e}")
            # Fallback to simple keyword search if model fails
            self.model = None
            self.load_documents()

    def load_documents(self):
        """Load legal documents from JSON file or use fallback sample data."""
        try:
            json_path = Path(__file__).parent / 'legal_documents.json'
            if json_path.exists():
                with open(json_path, 'r', encoding='utf-8') as f:
                    data = json.load(f)
                    self.documents = data.get('documents', [])
                logging.info(f"Loaded {len(self.documents)} documents from JSON file")
            else:
                # Fallback sample data
                self.documents = self.get_sample_documents()
                logging.warning(f"JSON file not found, using {len(self.documents)} sample documents")
            
            # Pre-compute embeddings for all documents if model is available
            if self.model and self.documents:
                texts = [f"{doc['title']}. {doc.get('content', doc.get('summary', ''))}" for doc in self.documents]
                self.embeddings = self.model.encode(texts)
                logging.info(f"Computed embeddings for {len(self.documents)} documents")
                
        except json.JSONDecodeError as e:
            logging.error(f"Invalid JSON in documents file: {e}")
            self.documents = self.get_sample_documents()
        except Exception as e:
            logging.error(f"Error loading documents: {e}")
            self.documents = self.get_sample_documents()

    def get_sample_documents(self):
        """Return sample legal documents as fallback."""
        return [
            {
                "id": 1,
                "title": "Miranda v. Arizona",
                "content": "The Supreme Court held that the prosecution may not use statements arising from custodial interrogation of the defendant unless it demonstrates the use of procedural safeguards effective to secure the privilege against self-incrimination. The Court specified that before any custodial questioning, the person must be warned that they have the right to remain silent, that any statement they make may be used as evidence against them, and that they have the right to the presence of an attorney.",
                "summary": "Established that police must inform suspects of their rights before custodial interrogation.",
                "tags": ["Criminal Law", "Constitutional Law", "Police Procedure"],
                "year": 1966
            },
            {
                "id": 2,
                "title": "Gideon v. Wainwright", 
                "content": "The Supreme Court unanimously ruled that states are required under the Sixth Amendment to provide an attorney to defendants in criminal cases who are unable to afford their own attorneys. The case extended the right to counsel to indigent defendants in state courts, dramatically improving the quality of justice in the United States.",
                "summary": "Established right to counsel for criminal defendants who cannot afford an attorney.",
                "tags": ["Criminal Law", "Constitutional Law", "Right to Counsel"],
                "year": 1963
            },
            {
                "id": 3,
                "title": "Brown v. Board of Education",
                "content": "The Supreme Court unanimously held that separate educational facilities are inherently unequal, effectively overturning Plessy v. Ferguson and the separate but equal doctrine. The Court found that segregation of children in public schools solely on the basis of race deprives children of the minority group of equal educational opportunities.",
                "summary": "Ruled that racial segregation in public schools is unconstitutional.",
                "tags": ["Civil Rights", "Constitutional Law", "Education Law"],
                "year": 1954
            },
            {
                "id": 4,
                "title": "Mapp v. Ohio",
                "content": "The Supreme Court ruled that evidence obtained in violation of the Fourth Amendment protection against unreasonable searches and seizures may not be used in state law criminal prosecutions in state courts, as well as federal criminal law prosecutions in federal courts. This doctrine is known as the exclusionary rule.",
                "summary": "Extended the exclusionary rule to state courts, prohibiting the use of illegally obtained evidence.",
                "tags": ["Criminal Law", "Constitutional Law", "Search and Seizure"], 
                "year": 1961
            },
            {
                "id": 5,
                "title": "Terry v. Ohio",
                "content": "The Supreme Court held that the Fourth Amendment prohibition on unreasonable searches and seizures is not violated when a police officer stops a suspect on the street and frisks them without probable cause to arrest, if the police officer has a reasonable suspicion that the person has committed, is committing, or is about to commit a crime and has a reasonable belief that the person may be armed and presently dangerous.",
                "summary": "Established the standard for stop and frisk procedures by police.",
                "tags": ["Criminal Law", "Constitutional Law", "Police Procedure"],
                "year": 1968
            },
            {
                "id": 6,
                "title": "Roe v. Wade",
                "content": "The Supreme Court ruled that the Constitution of the United States protects a pregnant woman's liberty to choose to have an abortion without excessive government restriction. The decision struck down many federal and state abortion laws, and it caused an ongoing abortion debate in the United States about whether, or to what extent, abortion should be legal.",
                "summary": "Established constitutional right to abortion under the Due Process Clause of the Fourteenth Amendment.",
                "tags": ["Constitutional Law", "Privacy Rights", "Due Process"],
                "year": 1973
            },
            {
                "id": 7,
                "title": "Marbury v. Madison",
                "content": "The Supreme Court established the principle of judicial review, which gives the Court the power to declare acts of Congress unconstitutional. Chief Justice John Marshall wrote that it is the duty of the judicial department to say what the law is, establishing the Court as the final arbiter of constitutional questions.",
                "summary": "Established the principle of judicial review in the United States.",
                "tags": ["Constitutional Law", "Judicial Review", "Separation of Powers"],
                "year": 1803
            }
        ]

    def search(self, query, top_k=5, min_score=0.1):
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
            logging.info(f"Performing search for query: '{query}' with top_k={top_k}, min_score={min_score}")
            
            if not self.documents:
                logging.warning("No documents available for search")
                return []

            # If we have a model and embeddings, use semantic search
            if self.model and self.embeddings is not None:
                return self._semantic_search(query, top_k, min_score)
            else:
                # Fallback to keyword search
                logging.info("Using fallback keyword search")
                return self._keyword_search(query, top_k)
                
        except Exception as e:
            logging.error(f"Search error: {e}")
            # Return keyword search as ultimate fallback
            return self._keyword_search(query, top_k)

    def _semantic_search(self, query, top_k, min_score):
        """Perform semantic search using embeddings."""
        # Encode the query
        query_embedding = self.model.encode([query])
        
        # Calculate cosine similarity
        similarities = cosine_similarity(query_embedding, self.embeddings)[0]
        
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
                'summary': doc.get('summary', doc.get('content', '')[:200] + '...'),
                'similarity_score': score,
                'tags': doc.get('tags', []),
                'year': doc.get('year', 'N/A')
            })
            
            if len(results) >= top_k:
                break
        
        logging.info(f"Semantic search returned {len(results)} results")
        return results

    def _keyword_search(self, query, top_k):
        """Fallback keyword-based search."""
        query_lower = query.lower()
        query_words = query_lower.split()
        
        scored_docs = []
        for doc in self.documents:
            search_text = f"{doc['title']} {doc.get('content', '')} {doc.get('summary', '')} {' '.join(doc.get('tags', []))}".lower()
            
            # Simple scoring based on word matches
            score = 0
            for word in query_words:
                if word in search_text:
                    score += 1
            
            if score > 0:
                normalized_score = min(0.95, score / len(query_words))
                scored_docs.append((doc, normalized_score))
        
        # Sort by score and return top results
        scored_docs.sort(key=lambda x: x[1], reverse=True)
        
        results = []
        for doc, score in scored_docs[:top_k]:
            results.append({
                'title': doc['title'],
                'summary': doc.get('summary', doc.get('content', '')[:200] + '...'),
                'similarity_score': score,
                'tags': doc.get('tags', []),
                'year': doc.get('year', 'N/A')
            })
        
        logging.info(f"Keyword search returned {len(results)} results")
        return results

def main():
    """Main function for command-line usage."""
    try:
        # Check if input is available on stdin (for proc_open usage)
        if not sys.stdin.isatty():
            # Read from stdin for proc_open usage
            stdin_data = sys.stdin.read().strip()
            if stdin_data:
                try:
                    input_data = json.loads(stdin_data)
                    query = input_data.get('query', '')
                    top_k = input_data.get('top_k', 5)
                    min_score = input_data.get('min_score', 0.1)
                except json.JSONDecodeError:
                    # Fallback: treat stdin as plain query
                    query = stdin_data
                    top_k = 5
                    min_score = 0.1
            else:
                raise ValueError("No input provided via stdin")
        else:
            # Use command line arguments
            parser = argparse.ArgumentParser(description='Legal document semantic search')
            parser.add_argument('query', help='The search query')
            parser.add_argument('--top_k', type=int, default=5, help='Number of results to return')
            parser.add_argument('--min_score', type=float, default=0.1, help='Minimum similarity score threshold')
            
            args = parser.parse_args()
            query = args.query
            top_k = args.top_k
            min_score = args.min_score
        
        if not query:
            raise ValueError("Query cannot be empty")
            
        # Initialize search engine
        search_engine = LegalSearchEngine()
        
        # Perform search
        results = search_engine.search(query, top_k, min_score)
        
        # Prepare response
        response = {
            'status': 'success',
            'results': results,
            'query': query,
            'count': len(results),
            'search_method': 'semantic' if search_engine.model else 'keyword'
        }
        
        # Output JSON to stdout
        print(json.dumps(response, ensure_ascii=False, indent=None))
        return 0
        
    except Exception as e:
        # Output error as JSON
        error_response = {
            'status': 'error',
            'message': str(e),
            'query': locals().get('query', 'unknown')
        }
        print(json.dumps(error_response, ensure_ascii=False), file=sys.stderr)
        return 1

if __name__ == '__main__':
    sys.exit(main())
