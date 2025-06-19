### LexiAid Local Development Setup

1. Install XAMPP from https://www.apachefriends.org/download.html
2. Copy the LexiAid files to your XAMPP htdocs folder
3. Start Apache from XAMPP Control Panel
4. Access the site at http://localhost/lexiaid/

### Required Software
- XAMPP (for PHP and Apache)
- Python 3.8 or higher
- pip (Python package manager)

### Python Dependencies
```bash
pip install -r requirements.txt
```

### Configuration
1. Configure database settings in config/database.php
2. Set up legal_documents.json in the python folder

### Testing
1. Test semantic search: `python semantic_search.py "test query"`
2. Test PHP endpoint: Access http://localhost/lexiaid/search.php
3. Test full integration: Open http://localhost/lexiaid/search.html
