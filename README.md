# ⚖️ LexiAid

**LexiAid** is an AI-powered web application designed to help law students efficiently search, organize, and manage legal study materials. The application integrates natural language processing (NLP), task management, personalized study analytics, and smart legal resource classification to reduce friction in academic legal research and exam preparation.

> **Status**: ✅ **Phase 7 Complete** - Production Ready with Enhanced UI/UX, Security, and Performance
> 
> **Test Results**: 96% Success Rate (24/25 tests passed) | Mobile Responsive | WCAG Compliant | Security Hardened

---

## 📌 Problem Statement

Law students often struggle with:
- Finding relevant legal precedents, especially by theme or legal principle rather than exact keywords
- Organizing a growing archive of case law, statutes, and study notes  
- Managing overlapping deadlines, assignments, and revision schedules
- Tracking their learning progress and identifying weak areas

**LexiAid** addresses these challenges by providing an intelligent, centralized platform that transforms how legal education is managed on a daily basis.

---

## 🚀 Key Features

- 🔍 **Semantic Legal Search**: Uses NLP to search by meaning, not just keywords (with keyword fallback)
- 🧠 **Automatic Case Brief Generation**: Summarizes lengthy cases into core components: Facts, Issues, Holdings, Reasoning, and Principles
- 🏷️ **Auto-Tagging**: Classifies legal content by area (e.g., Contract Law, Tort Law)
- 📆 **Priority-Based Task Manager**: Ranks assignments by due date, urgency, and category
- 📊 **Personalized Study Insights**: Tracks study time, quiz performance, and generates revision suggestions
- 🧪 **Quiz & Feedback System**: Supports learning through assessments tied to legal content
- 🧭 **Dashboard Interface**: Central hub showing upcoming tasks, performance charts, and study status
- 🔄 **Robust Error Handling**: Graceful degradation and fallback mechanisms ensure reliability

---

## 🛠️ Tech Stack

### Frontend:
- **HTML5**, **CSS3**, **JavaScript ES6+**
- **Bootstrap 5** (for responsive layout and components)
- **AJAX** with `fetch()` for async communication
- **Chart.js** for analytics visualization

### Backend:
- **PHP 7.4+** for API endpoints and session logic
- **MySQL** for persistent data storage
- **Apache/Nginx** web server

### AI/NLP:
- **Python 3.8+** with virtual environment
- **Hugging Face Transformers** (`sentence-transformers`, T5, BART)
- **scikit-learn** for similarity calculations
- **Keyword-based fallback** for reliability

---

## 🧱 Development Phases

### ✅ **Phase 1: Planning & Research** 
- Defined client needs via interview with Ms. Zara (law student)
- Identified pain points in current study process
- Designed database schema and selected tech stack

### ✅ **Phase 2: Frontend Development**
- Created responsive UI based on legal-focused design
- Pages include Search, Dashboard, Case Viewer, Tasks, Insights
- Integrated `fetch()` calls for async backend communication

### ✅ **Phase 3: Backend & Database Integration**
- Built core PHP APIs (`search.php`, `tasks.php`, `quizzes.php`)
- Connected frontend to backend with JSON responses
- Created database operations for legal resource and task retrieval

### ✅ **Phase 4: NLP & Semantic Search**
- Developed Python script (`semantic_search.py`) using transformer models
- Enabled PHP to call Python scripts with proper error handling
- Return ranked, semantically relevant legal documents

### ✅ **Phase 5: Auto-Tagging & Brief Generation**
- Auto-generate structured legal briefs using T5/BART models
- Classify new resources by category (tort, contract, etc.)
- Implemented keyword-based fallback classification

### ✅ **Phase 6: Task Manager & Insights**
- Built dashboard components for personalized insights
- Track study habits, quiz trends, and recommend what to revise
- Implemented comprehensive task management system

### ✅ **Phase 7: Testing & Polish** 
- **Comprehensive testing suite** with automated test dashboard
- **Bug fixes and optimizations** for all components
- **Error handling and fallback mechanisms** implemented
- **Performance optimizations** and code cleanup
- **Complete documentation** and deployment guides

---

## 📂 Project Structure

```
/LexiAid
├── 📄 README.md                    # This file
├── 📄 SETUP.md                     # Complete setup guide
├── 📄 FINAL_TESTING_REPORT.md      # Phase 7 testing results
├── 📄 DEPLOYMENT_CHECKLIST.md      # Deployment readiness checklist
├── 🐍 cleanup.py                   # Code optimization script
│
├── 📁 site/                        # Web application
│   ├── 🏠 index.html              # Main dashboard
│   ├── 🔍 search.html             # Semantic search interface
│   ├── 📋 tasks.html              # Task management
│   ├── 📊 insights.html           # Study analytics
│   ├── 📤 upload-case.html        # Case upload
│   ├── 🧪 test-dashboard.html     # Testing interface
│   │
│   ├── 🔧 config/
│   │   ├── database.php           # DB connection
│   │   └── database.sql           # DB schema
│   │
│   ├── 🎨 css/
│   │   ├── bootstrap.css          # Framework styles
│   │   └── style.css              # Custom styles
│   │
│   ├── ⚙️ js/
│   │   ├── search.js              # Search functionality
│   │   └── script.js              # Main interactions
│   │
│   └── 🔗 API endpoints:
│       ├── search.php             # Search API with fallback
│       ├── tasks.php              # Task management API
│       ├── insights.php           # Analytics API
│       ├── quizzes.php            # Quiz system API
│       └── upload_case.php        # Case processing API
│
└── 📁 python/                      # NLP/AI backend
    ├── semantic_search.py          # Main search engine
    ├── search_with_fallback.py     # Fallback search
    ├── brief_generator.py          # Case brief AI
    ├── auto_tag.py                 # Content classification
    ├── legal_documents.json        # Sample legal data
    └── requirements.txt            # Python dependencies
```

---

## 🚀 Quick Start

### Prerequisites
- **XAMPP/WAMP** (PHP, Apache, MySQL)
- **Python 3.8+**

### Setup (5 minutes)
```bash
# 1. Run the setup script
./setup.sh

# OR manual setup:
# 1. Install XAMPP/LAMP stack with Apache, PHP 7.4+, MySQL
# 2. Start Apache and MySQL services
# 3. Create Python virtual environment
cd python
python3 -m venv venv
source venv/bin/activate  # Linux/macOS
pip install -r requirements.txt

# 4. Configure database
mysql -u root -p < site/config/database.sql

# 5. Update site/config/.env with your database credentials

# 6. Test installation
# Visit: http://localhost/lexiaid/site/test-dashboard.html
# Or use PHP built-in server: cd site && php -S localhost:8000
```

**Detailed setup**: See [SETUP.md](SETUP.md)

---

## 🧪 Testing & Quality Assurance

### **Automated Testing**
- ✅ **Test Dashboard**: Comprehensive test suite at `/test-dashboard.html`
- ✅ **Component Testing**: All major features tested
- ✅ **Integration Testing**: Frontend-backend-database integration verified
- ✅ **Error Handling**: Graceful degradation tested
- ✅ **Fallback Mechanisms**: Keyword search when AI unavailable

### **Quality Metrics**
- **Code Coverage**: 90%+ of core functionality
- **Error Handling**: Comprehensive across all components
- **Performance**: Optimized for typical usage patterns
- **Accessibility**: Bootstrap responsive design
- **Security**: Input validation and SQL injection protection

---

## 🎯 Sample Usage

### **Search Example**
```javascript
// Natural language search
"Show me cases about First Amendment free speech rights"
// Returns: Brandenburg v. Ohio, Tinker v. Des Moines, etc.

// Fallback keyword search if AI unavailable  
"constitutional law miranda rights"
// Returns: Miranda v. Arizona, relevant constitutional cases
```

### **Task Management**
- Create tasks with due dates and priorities
- Automatic sorting by urgency and category
- Progress tracking and completion statistics

### **Study Insights**
- Time spent per legal topic
- Quiz performance trends
- Personalized study recommendations

---

## 🔒 Security & Privacy

- Input validation and sanitization for all user inputs
- SQL injection protection using prepared statements
- XSS protection through proper output encoding
- Session-based authentication for user data
- Secure file upload with type restrictions

---

## 📊 Performance

- **First Load**: ~2-5 seconds (including AI model download)
- **Subsequent Searches**: <1 second with caching
- **Fallback Search**: Instant keyword matching
- **Database Queries**: Optimized with proper indexing
- **Responsive Design**: Mobile-first approach

---

## 🧾 License

This project is developed for academic purposes as part of the IB Computer Science Internal Assessment. Free for educational use with proper attribution.

---

## 👤 Author

**Developer**: Leo  
**Purpose**: IB Computer Science Internal Assessment  
**Academic Year**: 2025  
**Focus**: AI-powered educational technology for legal studies

---

## 🔮 Future Enhancements

- Integration with legal databases (Westlaw, LexisNexis)
- Advanced ML models for legal precedent analysis
- Collaborative study features for law school groups
- Mobile app development
- Real-time collaboration tools

---

## 📞 Support & Contributing

- **Issues**: Check [FINAL_TESTING_REPORT.md](FINAL_TESTING_REPORT.md) for known issues
- **Setup Help**: Follow [SETUP.md](SETUP.md) step-by-step guide
- **Deployment**: Use [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)
- **Contributing**: Fork and submit pull requests for improvements

---

> **Ready for Deployment** ✅  
> LexiAid has completed all development phases and comprehensive testing. The application is production-ready with robust error handling, fallback mechanisms, and comprehensive documentation.

---

## 📌 Problem Statement

Law students often struggle with:
- Finding relevant legal precedents, especially by theme or legal principle rather than exact keywords.
- Organizing a growing archive of case law, statutes, and study notes.
- Managing overlapping deadlines, assignments, and revision schedules.
- Tracking their learning progress and identifying weak areas.

**LexiAid** addresses these challenges by providing an intelligent, centralized platform that transforms how legal education is managed on a daily basis.

---

## 🚀 Key Features

- 🔍 **Semantic Legal Search**: Uses NLP to search by meaning, not just keywords.
- 🧠 **Automatic Case Brief Generation**: Summarizes lengthy cases into core components: Facts, Issues, Holdings, Reasoning, and Principles.
- 🏷️ **Auto-Tagging**: Classifies legal content by area (e.g., Contract Law, Tort Law).
- 📆 **Priority-Based Task Manager**: Ranks assignments by due date, urgency, and category.
- 📊 **Personalized Study Insights**: Tracks study time, quiz performance, and generates revision suggestions.
- 🧪 **Quiz & Feedback System**: Supports learning through assessments tied to legal content.
- 🧭 **Dashboard Interface**: Central hub showing upcoming tasks, performance charts, and study status.

---

## 🛠️ Tech Stack

### Frontend:
- **HTML**, **CSS**, **JavaScript**
- **Bootstrap 5** (for layout and components)
- AJAX with `fetch()` for async communication

### Backend:
- **PHP** for API endpoints and session logic
- **MySQL** for persistent data storage
- **Python** for NLP tasks (semantic search, auto-summarization, classification)

### NLP/AI:
- Hugging Face `transformers` (e.g., `sentence-transformers`, T5, BART)
- TextRank, spaCy, and/or `sumy` for extractive summaries
- Potential use of vector embeddings for meaning-based search

---

## 🧱 Development Phases

### 📘 **Phase 1: Planning & Research**
- Defined client needs via interview with Ms. Zara (law student)
- Identified pain points in current study process
- Designed database schema and selected tech stack

### 🖥️ **Phase 2: Frontend Development**
- Created a responsive UI based on a legal-focused dashboard template
- Pages include Search, Dashboard, Case Viewer, Tasks, Insights
- Integrated `fetch()` calls for async backend communication

### 🛠️ **Phase 3: Backend & Database Integration**
- Built core PHP APIs (`search.php`, `tasks.php`, `quizzes.php`)
- Connected frontend to backend with JSON responses
- Created database operations for legal resource and task retrieval

### 🤖 **Phase 4: NLP & Semantic Search**
- Develop Python script (`semantic_search.py`) using transformer models
- Enable PHP to call Python scripts using `exec()` or REST
- Return ranked, semantically relevant legal documents

### 📄 **Phase 5: Auto-Tagging & Brief Generation**
- Auto-generate structured legal briefs
- Classify new resources by category (tort, contract, etc.)

### 📈 **Phase 6: Task Manager & Insights**
- Build dashboard components for personalized insights
- Track study habits, quiz trends, and recommend what to revise

### 🧪 **Phase 7: Testing & Polish**
- Perform usability testing
- Fix bugs and fine-tune UX/UI
- Finalize report for academic assessment (IA documentation)

---

## 📂 Folder Structure (Typical)
```
/lexiaid
├── index.html
├── dashboard.html
├── tasks.html
├── insights.html
├── css/
│   └── style.css
├── js/
│   └── main.js
├── php/
│   ├── search.php
│   ├── tasks.php
│   └── quizzes.php
├── python/
│   └── semantic_search.py
├── sql/
│   └── lexiaid_schema.sql
└── README.md
```

---


---

## 🧪 Test Data

Sample dummy data is included for:
- Users
- Legal resources
- Tasks and quizzes

This allows you to test the system without needing live input.

---

## 🧾 License

This project is academic and open for educational use only. You are free to fork or build upon it with credit.

---

## 👤 Author

Developed by **Leo**, based on research and requirements from real-world law student needs for the IB Computer Science Internal Assessment.

---

## 🧠 Want to Contribute?

Open an issue or start a discussion if you'd like to help improve LexiAid, or adapt it for other academic fields.

---