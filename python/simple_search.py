#!/usr/bin/env python3
"""Simple test for the semantic search functionality"""

import sys
import json

def simple_keyword_search(query, documents):
    """Simple keyword-based search fallback."""
    query_lower = query.lower()
    query_words = query_lower.split()
    
    scored_docs = []
    for doc in documents:
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
    for doc, score in scored_docs[:5]:  # Top 5 results
        results.append({
            'title': doc['title'],
            'summary': doc.get('summary', doc.get('content', '')[:200] + '...'),
            'similarity_score': score,
            'tags': doc.get('tags', []),
            'year': doc.get('year', 'N/A')
        })
    
    return results

def main():
    # Sample documents
    documents = [
        {
            "id": 1,
            "title": "Miranda v. Arizona",
            "content": "The Supreme Court held that the prosecution may not use statements arising from custodial interrogation of the defendant unless it demonstrates the use of procedural safeguards effective to secure the privilege against self-incrimination.",
            "summary": "Established that police must inform suspects of their rights before custodial interrogation.",
            "tags": ["Criminal Law", "Constitutional Law", "Police Procedure"],
            "year": 1966
        },
        {
            "id": 2,
            "title": "Gideon v. Wainwright", 
            "content": "The Supreme Court unanimously ruled that states are required under the Sixth Amendment to provide an attorney to defendants in criminal cases who are unable to afford their own attorneys.",
            "summary": "Established right to counsel for criminal defendants who cannot afford an attorney.",
            "tags": ["Criminal Law", "Constitutional Law", "Right to Counsel"],
            "year": 1963
        },
        {
            "id": 3,
            "title": "Brown v. Board of Education",
            "content": "The Supreme Court unanimously held that separate educational facilities are inherently unequal, effectively overturning Plessy v. Ferguson and the separate but equal doctrine.",
            "summary": "Ruled that racial segregation in public schools is unconstitutional.",
            "tags": ["Civil Rights", "Constitutional Law", "Education Law"],
            "year": 1954
        },
        {
            "id": 4,
            "title": "Mapp v. Ohio",
            "content": "The Supreme Court ruled that evidence obtained in violation of the Fourth Amendment protection against unreasonable searches and seizures may not be used in state law criminal prosecutions.",
            "summary": "Extended the exclusionary rule to state courts, prohibiting the use of illegally obtained evidence.",
            "tags": ["Criminal Law", "Constitutional Law", "Search and Seizure"], 
            "year": 1961
        },
        {
            "id": 5,
            "title": "Terry v. Ohio",
            "content": "The Supreme Court held that the Fourth Amendment prohibition on unreasonable searches and seizures is not violated when a police officer stops a suspect on the street and frisks them without probable cause to arrest.",
            "summary": "Established the standard for stop and frisk procedures by police.",
            "tags": ["Criminal Law", "Constitutional Law", "Police Procedure"],
            "year": 1968
        }
    ]
    
    # Get query from command line
    if len(sys.argv) < 2:
        query = "constitutional law"
    else:
        query = sys.argv[1]
    
    # Perform search
    results = simple_keyword_search(query, documents)
    
    # Return JSON response
    response = {
        'status': 'success',
        'results': results,
        'query': query,
        'count': len(results),
        'search_method': 'keyword_fallback'
    }
    
    print(json.dumps(response, ensure_ascii=False))

if __name__ == '__main__':
    main()
