# MyPortfolio-
Your portfolio is the bridge between your academic life and your professional career. Build it strong
# Portfolio Project Report
## Mariem Mrabet – Full-Stack Web Portfolio

---

### 1. Project Overview
A comprehensive full-stack web portfolio showcasing my skills,
projects, and professional background. Built with HTML5, CSS3,
JavaScript, PHP, and MySQL. Features multilingual support in
English, French, Arabic, and Turkish.

---

### 2. Technologies Used
| Layer       | Technology           |
|-------------|----------------------|
| Frontend    | HTML5, CSS3 (Flexbox/Grid), JavaScript ES6+ |
| Backend     | PHP 8+               |
| Database    | MySQL 8              |
| Icons       | Font Awesome 6       |
| Fonts       | Google Fonts (Poppins, Amiri) |

---

### 3. Features Implemented

#### HTML & CSS
- Semantic HTML5 elements (section, nav, header, footer, article)
- Responsive design using CSS Grid and Flexbox
- CSS Custom Properties for theming
- External stylesheet with consistent branding
- HTML tables for about section data
- Contact form with all required fields

#### JavaScript / DOM
- Typed text animation
- Dark mode toggle (persisted via cookies)
- Language switcher (EN/FR/AR/TR with RTL for Arabic)
- Smooth scroll and navbar behavior
- Intersection Observer for scroll animations
- Skill bar animations on scroll
- Project filter system
- Project modal
- Form validation (real-time + on submit)
- Loader animation

#### PHP / MySQL
- Contact form saves to MySQL database
- Projects fetched dynamically via AJAX (Fetch API)
- Server-side validation and sanitization
- Rate limiting on contact form (session-based)
- Prepared statements to prevent SQL injection
- Admin CRUD operations for projects

#### Sessions & Cookies
- Admin authentication with PHP sessions
- Session regeneration on login
- Dark mode preference stored in cookie (365 days)
- Language preference stored in cookie (365 days)
- Remember-me cookie (30 days) on admin login
- Rate limiting using session storage

---

### 4. Security Measures
- Password hashing with bcrypt (password_hash / password_verify)
- Prepared statements (PDO-style with mysqli)
- Input sanitization (strip_tags, htmlspecialchars)
- Session fixation prevention (session_regenerate_id)
- CSRF protection via session checks
- HttpOnly admin cookies

---

### 5. File Structure
