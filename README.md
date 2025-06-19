# ⚖️ LexiAid

LexiAid is an AI-powered web application built to help law students efficiently search, organize, and manage legal study materials. The tool integrates natural language processing (NLP), task management, personalized study analytics, and smart legal resource classification to reduce friction in academic legal research and exam preparation.

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