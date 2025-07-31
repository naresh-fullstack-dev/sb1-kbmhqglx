# StudyFetch Clone - Educational Platform

A comprehensive educational platform built with PHP, MySQL, HTML, CSS, and Tailwind CSS that allows users to upload PDFs, generate questions, create flashcards, and interact with an AI chatbot.

## Features

### 🔒 User Roles
- **Admin**: Can upload PDF files and manage the platform
- **Student/User**: Can register, login, view PDFs, and use all study tools

### 📁 PDF Management System
- Admin-only PDF upload with metadata (title, subject, chapter)
- Secure file storage and management
- PDF listing and organization

### 🧠 Question Generation
- Select multiple PDFs for question generation
- Customizable difficulty levels (easy, medium, hard)
- Multiple question types: MCQs, short answer, long answer, fill in the blanks, true/false, assertion-reason
- Configurable marks per question
- Download generated questions

### 🃏 Flashcard System
- Generate flashcards from selected PDFs
- Interactive flip animation
- Navigation controls (previous, next, shuffle)
- Keyboard navigation support
- Progress tracking

### 💬 FetchGPT (AI Chatbot)
- Chat interface for asking questions about study materials
- Context-aware responses based on uploaded PDFs
- Chat history storage and retrieval
- Quick question suggestions

### 📊 Dashboard
- User progress overview
- Quick access to all features
- Recent activity tracking
- Statistics display

## Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Tailwind CSS
- **JavaScript**: Vanilla JS (ES6+)
- **Icons**: Font Awesome 6.0
- **Server**: Apache (WampServer/XAMPP compatible)

## Installation

### Prerequisites
- WampServer (or XAMPP) with PHP 7.4+ and MySQL
- Web browser (Chrome, Firefox, Safari, Edge)

### Setup Instructions

1. **Download and Extract**
   - Extract the project files to your `www` directory (typically `C:\wamp64\www\studyfetch`)

2. **Database Setup**
   - Start WampServer (Apache and MySQL services)
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Run the SQL commands from `supabase/migrations/20250731170323_long_trail.sql`
   - The database will be created with sample data including an admin user

3. **Configuration**
   - The database configuration is in `backend/db.php`
   - Default settings work with standard WampServer installation
   - Modify if you have custom database credentials

4. **Permissions**
   - Ensure the `uploads/` directory is writable
   - The `.htaccess` file provides security for uploaded files

5. **Access the Application**
   - Navigate to `http://localhost/studyfetch/`
   - Default admin credentials:
     - Email: `admin@studyfetch.com`
     - Password: `admin123`

## Project Structure

```
studyfetch/
├── backend/
│   ├── db.php                 # Database connection
│   ├── auth/
│   │   └── login.php          # Authentication logic
│   ├── pdfs/
│   │   ├── list_pdfs.php      # List all PDFs
│   │   └── upload_pdf.php     # Handle PDF uploads
│   ├── generate/
│   │   └── questions.php      # Question generation
│   ├── chat/
│   │   └── fetchgpt.php       # AI chatbot backend
│   └── flashcards/
│       └── get_flashcards.php # Flashcard management
├── database/
│   └── schema.sql             # Database structure
├── uploads/                   # PDF file storage
│   └── .htaccess             # Security rules
├── index.html                 # Landing page
├── dashboard.html             # User dashboard
├── upload.html                # PDF upload (admin)
├── generate_questions.html    # Question generator
├── flashcards.html           # Flashcard viewer
├── fetchgpt.html             # AI chatbot interface
├── test_generator.html       # Future feature page
└── README.md                 # This file
```

## Database Schema

### Tables
- **users**: User accounts and roles
- **pdfs**: Uploaded PDF metadata
- **questions**: Generated questions
- **flashcards**: Study flashcards
- **chat_logs**: AI chat history

## Key Features Explained

### Security
- Role-based access control
- Secure file upload with type validation
- SQL injection prevention with PDO
- XSS protection with proper encoding
- Secure file storage with .htaccess protection

### User Experience
- Responsive design for all screen sizes
- Smooth animations and transitions
- Keyboard navigation support
- Progressive loading states
- Error handling with user-friendly messages

### AI Integration Ready
- Full OpenAI API integration via OpenRouter
- Real-time AI responses for chat and content generation
- Extensible chat system for future AI enhancements

## Development Notes

### Extending the Application
- OpenAI API integrated and ready to use
- Implement actual PDF text extraction for better question generation
- Add more question types and complexity levels
- Implement user progress tracking and analytics

### Security Considerations
- Change default admin credentials in production
- Use environment variables for sensitive configuration
- Implement rate limiting for API endpoints
- Add CSRF protection for forms
- Use HTTPS in production

## Future Enhancements

- **Test Generator**: Complete testing system with timed tests
- **Progress Analytics**: Detailed learning progress tracking
- **Collaborative Features**: Study groups and sharing
- **Mobile App**: React Native or Flutter mobile application
- **Advanced AI**: GPT-4 integration for better responses
- **Export Options**: Various export formats for questions and tests

## Support

For questions or issues:
1. Check the browser console for JavaScript errors
2. Verify database connection in `backend/db.php`
3. Ensure proper file permissions for uploads
4. Check Apache error logs for server-side issues

## License

This project is for educational purposes. Feel free to use and modify as needed for learning and development.