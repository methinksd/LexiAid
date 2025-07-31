# LexiAid - Computer Science Internal Assessment
## Rewritten Technology Justification

---

## Criterion A: Planning and Analysis

To develop the web-based legal assistant tool, I chose **HTML5, CSS3, and JavaScript** for the frontend due to their universal browser compatibility and efficient rendering capabilities for displaying large legal texts. The client specifically needed this for ease of reading all legal documents and case studies (refer to appendix A - 1.2). **Bootstrap 5** was selected as the CSS framework to ensure responsive design across different devices, which was essential as law students often switch between desktop and mobile devices during research sessions.

For enhanced user interaction, I implemented **Chart.js** for data visualization to display study analytics and performance metrics. The frontend uses **AJAX with fetch() API** for asynchronous communication with the backend, preventing page reloads and providing a smooth user experience when performing searches or managing tasks.

The client required robust document search functionality, so I chose **JavaScript ES6+** features including arrow functions, promises, and async/await patterns to handle complex search operations and API interactions efficiently. This choice enabled real-time search suggestions and seamless integration with the semantic search backend.

---

## Criterion B: Design and Development  

For the backend, I selected **PHP 7.4+** for its simplicity and strong compatibility with web servers, making deployment straightforward for educational institutions. PHP enables efficient handling of HTTP requests through dedicated API endpoints (`search.php`, `tasks.php`, `quizzes.php`, `insights.php`) that return JSON responses for frontend consumption.

**MySQL** was chosen as the database management system due to its reliability, ACID compliance, and excellent support for full-text search operations on legal documents. The database schema includes specialized tables for `legal_resources`, `tasks`, `quizzes`, and `users`, with proper indexing for performance optimization. MySQL's JSON data type support allows flexible storage of legal case tags and quiz metadata.

For the AI/NLP engine, I selected **Python 3.8+** due to its extensive machine learning ecosystem. The client requires semantic search functionality, allowing users to search by meaning rather than just keywords, which is a key requirement for easy resource gathering (refer to appendix B - 2.2). This is implemented using **Hugging Face Transformers** library with **sentence-transformers**, specifically the 'all-MiniLM-L6-v2' model for generating document embeddings.

The client also requires legal case summarization (refer to appendix B - 2.2), which is handled by **Transformer-based models** including **T5-base** and **BART** models through the `brief_generator.py` script. These models automatically generate structured case briefs with sections for Facts, Issues, Holdings, Reasoning, and Legal Principles.

**scikit-learn** was integrated for cosine similarity calculations between document embeddings, enabling accurate semantic matching. The system includes automatic fallback to keyword-based search when AI models are unavailable, ensuring system reliability.

---

## Criterion C: Implementation and Testing

Task management and deadline tracking are implemented through the MySQL database with a comprehensive `tasks` table supporting priority levels, categories, and completion status. The PHP backend provides CRUD operations through `tasks.php`, while the frontend JavaScript handles dynamic task filtering, sorting, and progress tracking.

To ensure system reliability, I implemented robust error handling throughout the application. The semantic search includes try-catch blocks that gracefully fall back to database-driven keyword search if Python models fail to load. All PHP endpoints include input validation and SQL injection protection using prepared statements.

The system architecture follows a multi-tier design pattern:
- **Frontend Layer**: HTML/CSS/JavaScript with Bootstrap
- **API Layer**: PHP endpoints for business logic
- **AI/NLP Layer**: Python scripts for semantic processing  
- **Data Layer**: MySQL database with optimized schemas

For auto-classification of legal content, I developed `auto_tag.py` using keyword matching and pattern recognition to categorize documents by legal area (Constitutional Law, Contract Law, Tort Law, etc.). This addresses the client's need for organized content management.

Performance optimization includes caching of document embeddings to avoid recomputation, database indexing on frequently searched fields, and compressed JSON responses from API endpoints. The testing framework includes both automated unit tests and a comprehensive test dashboard (`test-dashboard.html`) for end-to-end validation.

These technologies were selected for their open-source availability, development efficiency, and proven ability to handle large legal text datasets while maintaining fast response times for user queries.

---

## Appendix References

- **Appendix A - 1.2**: Client interview transcript showing requirement for easy document reading interface
- **Appendix B - 2.2**: Client specifications for semantic search and case summarization features

---

*This implementation successfully addresses all client requirements while maintaining scalability, reliability, and performance standards appropriate for academic legal research.*
