SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS blog_posts;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS project_requests;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS ai_solutions;
DROP TABLE IF EXISTS project_images;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS system_settings;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS sent_emails;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(40),
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user') DEFAULT 'user',
  status ENUM('active','blocked') DEFAULT 'active',
  is_activated TINYINT(1) NOT NULL DEFAULT 1,
  activation_token VARCHAR(100) NULL,
  activated_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sent_emails (
  id INT AUTO_INCREMENT PRIMARY KEY,
  recipient_email VARCHAR(190) NOT NULL,
  recipient_name VARCHAR(150),
  subject VARCHAR(255) NOT NULL,
  badge VARCHAR(80),
  message TEXT NOT NULL,
  status ENUM('sent','failed') DEFAULT 'sent',
  sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','super_admin') DEFAULT 'admin',
  status ENUM('active','blocked') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE system_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT,
  input_type VARCHAR(40) DEFAULT 'text',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  short_description VARCHAR(255) NOT NULL,
  full_description TEXT,
  icon VARCHAR(120) DEFAULT 'Code2',
  image VARCHAR(255),
  category VARCHAR(100) DEFAULT 'Software Development',
  status ENUM('active','inactive') DEFAULT 'active',
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  slug VARCHAR(190) NOT NULL UNIQUE,
  category VARCHAR(120) NOT NULL,
  short_description VARCHAR(255) NOT NULL,
  full_description TEXT,
  features TEXT,
  technologies VARCHAR(255),
  main_image VARCHAR(255),
  demo_link VARCHAR(255),
  client_name VARCHAR(150),
  completion_date DATE,
  status ENUM('completed','ongoing','upcoming') DEFAULT 'completed',
  featured TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  project_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  caption VARCHAR(190),
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (project_id),
  CONSTRAINT fk_project_images_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ai_solutions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  short_description VARCHAR(255) NOT NULL,
  full_description TEXT,
  icon VARCHAR(120) DEFAULT 'Bot',
  status ENUM('active','inactive') DEFAULT 'active',
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40),
  preferred_date DATE NOT NULL,
  preferred_time TIME NOT NULL,
  appointment_type VARCHAR(120) NOT NULL,
  message TEXT,
  status ENUM('pending','approved','rejected','completed') DEFAULT 'pending',
  admin_note TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (user_id),
  CONSTRAINT fk_appointments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE project_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  user_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40),
  company_name VARCHAR(160),
  project_type VARCHAR(120) NOT NULL,
  project_category VARCHAR(120) NOT NULL,
  budget_range VARCHAR(120),
  expected_delivery_date DATE,
  description TEXT NOT NULL,
  file_path VARCHAR(255),
  status ENUM('pending','reviewing','approved','rejected','in_progress','completed') DEFAULT 'pending',
  admin_feedback TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (user_id),
  CONSTRAINT fk_project_requests_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40),
  subject VARCHAR(180),
  message TEXT NOT NULL,
  status ENUM('new','read','replied','archived') DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_name VARCHAR(150) NOT NULL,
  position_company VARCHAR(180),
  message TEXT NOT NULL,
  rating TINYINT DEFAULT 5,
  image VARCHAR(255),
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE blog_posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(190) NOT NULL,
  slug VARCHAR(190) NOT NULL UNIQUE,
  category VARCHAR(100),
  featured_image VARCHAR(255),
  content LONGTEXT NOT NULL,
  author VARCHAR(150) DEFAULT 'Kendat Integrated Services',
  published_at DATE,
  status ENUM('draft','published','archived') DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  actor_type ENUM('admin','user','system') DEFAULT 'system',
  actor_id INT NULL,
  action VARCHAR(190) NOT NULL,
  entity_type VARCHAR(100),
  entity_id INT NULL,
  ip_address VARCHAR(64),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (full_name, email, phone, password_hash, role, status, is_activated) VALUES
('Tunde Adebayo', 'tunde.adebayo@company.ng', '+234 802 345 6789', '$2y$10$jv9r7Uz2uf.QIuXf2hIXnekjtIKmzHxrk/FDrcf9WW1mnUlskfq3G', 'user', 'active', 1);

INSERT INTO admins (full_name, email, password_hash, role) VALUES
('Muhammad Mukhtar', 'muhdmukhtar2019@gmail.com', '$2y$10$jv9r7Uz2uf.QIuXf2hIXnekjtIKmzHxrk/FDrcf9WW1mnUlskfq3G', 'super_admin');
-- Default password for both admin and client: 1234567890Aa@

INSERT INTO system_settings (setting_key, setting_value, input_type) VALUES
('website_name','Kendat Integrated Services','text'),
('company_name','Kendat Integrated Services','text'),
('website_slogan','Building Intelligent Digital Solutions for Businesses, Institutions, and the Future.','text'),
('about_company','Kendat Integrated Services is a technology-driven company focused on designing, developing, and deploying modern software solutions, AI-powered systems, enterprise platforms, and digital transformation tools for businesses, institutions, and individuals.','textarea'),
('mission_statement','To build reliable, intelligent, and scalable digital systems that help organizations operate faster, smarter, and more securely.','textarea'),
('vision_statement','To become a trusted technology partner for digital transformation across Africa and beyond.','textarea'),
('footer_text','© Kendat Integrated Services. Intelligent software for modern organizations.','text'),
('contact_email','hello@kendatservices.com','email'),
('contact_phone','+234 800 000 0000','text'),
('whatsapp_number','2348000000000','text'),
('office_address','Lagos, Nigeria','textarea'),
('facebook_link','#','url'),
('twitter_link','#','url'),
('instagram_link','#','url'),
('linkedin_link','#','url'),
('youtube_link','#','url'),
('hero_title','Kendat Integrated Services','text'),
('hero_subtitle','Innovative Software, AI, and Digital Transformation Solutions','textarea'),
('hero_background_image','settings/hero_bg.png','image'),
('logo','settings/settings_6a1468d5628ee7.05176596.png','image'),
('favicon','settings/settings_6a1468d562d829.71873466.png','image'),
('primary_color','#0087FF','color'),
('secondary_color','#00E5FF','color'),
('maintenance_mode','0','boolean'),
('years_experience','6','number'),
('completed_projects','120','number'),
('clients_served','85','number');

INSERT INTO services (title, short_description, full_description, icon, category, sort_order) VALUES
('Website Development','Responsive, secure, conversion-focused company websites and portals.','We design and engineer modern websites, client portals, CMS systems, and web applications with clean interfaces and reliable backend integrations.','Globe','Web Development',1),
('Mobile App Development','Android and cross-platform mobile apps for real business workflows.','From booking apps to dashboards and client-facing platforms, we build mobile products that connect smoothly to APIs, databases, and cloud services.','Smartphone','Mobile',2),
('AI Software Development','Custom AI-powered apps, assistants, and decision systems.','We build AI chatbots, knowledge assistants, automation copilots, and intelligent software features tailored to your domain.','Bot','AI',3),
('Machine Learning Model Development','Prediction, classification, forecasting, and optimization models.','We design data pipelines, train machine learning models, evaluate performance, and deploy models into production apps.','BrainCircuit','AI',4),
('School Portal Development','Complete student, staff, result, payment, and parent portal systems.','A full school management platform covering admissions, results, fees, attendance, messaging, and reporting.','GraduationCap','Education',5),
('Business Management System','Operational software for sales, inventory, HR, finance, and reporting.','We automate business operations with custom dashboards, workflows, approvals, permissions, and analytics.','BriefcaseBusiness','Enterprise',6),
('API Development','Secure REST APIs for apps, integrations, and third-party platforms.','We create documented, tested, and secure APIs with authentication, validation, logging, and versioning.','Network','Backend',7),
('Cloud Deployment','Deployment, hosting, backups, monitoring, and scaling support.','We deploy applications to cloud or VPS infrastructure with SSL, CI/CD, backups, and performance tuning.','Cloud','Cloud',8),
('Cybersecurity Tools','Security-focused utilities, access control, and audit dashboards.','We build practical security dashboards, access systems, log tools, and hardening workflows for digital products.','ShieldCheck','Security',9);

INSERT INTO projects (title, slug, category, short_description, full_description, features, technologies, main_image, demo_link, client_name, completion_date, status, featured) VALUES
('EduCore Smart School Portal','educore-smart-school-portal','School Portal','A complete digital school platform for results, payments, admissions, and parent communication.','EduCore is a full school administration platform designed for growing institutions that need fast result processing, fee tracking, parent access, and staff workflows.','Admissions management\nStudent result processing\nFee tracking\nParent and teacher dashboards\nRole-based access control','React, PHP, MySQL, REST API','projects/projects_6a14675868b2c8.49575753.png','#','Demo Academy','2025-09-15','completed',1),
('InsightFlow Analytics Dashboard','insightflow-analytics-dashboard','Data Analytics','A business intelligence dashboard for operational KPIs and forecasting.','InsightFlow consolidates business data into executive dashboards with filters, trend tracking, forecasting modules, and exportable reports.','KPI dashboards\nForecast widgets\nCSV import\nExecutive reports\nSecure user roles','React, PHP, MySQL, Chart.js, Machine Learning','projects/projects_6a1570d42efd04.84387159.png','#','Retail Group','2025-11-02','completed',1),
('AutoDeskOps Workflow Automation','autodeskops-workflow-automation','Automation','An internal automation system for approvals, tasks, and operational tracking.','AutoDeskOps helps teams reduce manual follow-ups with automated request pipelines, reminders, audit trails, and team dashboards.','Workflow builder\nApproval chains\nNotifications\nAudit logs\nTeam performance views','React, PHP, MySQL, REST API','projects/projects_6a156c0eaea917.04572701.png','#','Operations Firm','2026-02-20','ongoing',1);

INSERT INTO project_images (project_id, image_path, caption, sort_order) VALUES
(1,'projects/projects_6a1568abc5c533.86303239.png','Dashboard preview',1),
(1,'projects/projects_6a1568abc689b0.59299963.png','Results module',2),
(1,'projects/projects_6a1568abc883c9.32754331.png','Parent portal',3),
(2,'projects/projects_6a1568abc9daf6.42984791.png','KPI overview',1),
(2,'projects/projects_6a1568abcb49b5.05833956.png','Forecasting view',2),
(2,'projects/projects_6a1568abccac66.10558973.png','Report builder',3),
(3,'projects/projects_6a156c0eaee885.55449173.png','Workflow board',1),
(3,'projects/projects_6a156c0eaf7882.22972493.png','Approval detail',2),
(3,'projects/projects_6a156c0eb03a07.87078857.png','Activity log',3);

INSERT INTO ai_solutions (title, short_description, full_description, icon, sort_order) VALUES
('AI Chatbots','Business chatbots for support, onboarding, and knowledge access.','Deploy intelligent assistants that answer questions, qualify leads, and connect users to services.','Bot',1),
('Machine Learning Prediction Systems','Forecast demand, risk, revenue, churn, and operational outcomes.','We design prediction systems with proper data preparation, evaluation, and production APIs.','TrendingUp',2),
('Computer Vision Systems','Image recognition, inspection, detection, and verification workflows.','Use vision models for quality checks, document processing, monitoring, and recognition tasks.','ScanEye',3),
('Natural Language Processing Applications','Search, summarization, classification, and document intelligence.','Turn unstructured text into useful workflows for teams and customers.','MessageSquareText',4),
('AI Recommendation Systems','Personalized recommendations for products, content, and workflows.','Improve discovery and conversion with ranking and recommendation engines.','Sparkles',5),
('AI Business Automation','AI-assisted operations, reporting, routing, and decision support.','Connect AI to your internal systems to reduce repetitive work and improve response speed.','Workflow',6),
('Data Analytics and Forecasting','Dashboards with predictive analytics and automated insights.','Combine analytics with forecasting so managers can make faster decisions.','ChartNoAxesCombined',7),
('AI Research Model Development','Prototype, evaluate, and package custom research models.','We support model experimentation, evaluation, and practical software integration.','FlaskConical',8);

INSERT INTO testimonials (client_name, position_company, message, rating, status) VALUES
('Amaka Johnson','Operations Director, Prime Retail','Kendat helped us move from spreadsheets to a clean management system that our team actually enjoys using.',5,'active'),
('Tunde Adebayo','Administrator, Brightfield Schools','The school portal reduced result processing time dramatically and gave parents a better digital experience.',5,'active'),
('Mariam Bello','Founder, ScaleUp Studio','Their team understood the product idea quickly and built a polished platform with the right admin controls.',5,'active');

INSERT INTO blog_posts (title, slug, category, content, author, published_at, status) VALUES
('How AI Automation Helps Growing Businesses','how-ai-automation-helps-growing-businesses','AI','AI automation can help teams reduce repetitive work, improve response quality, and make better use of operational data.','Kendat Integrated Services','2026-01-10','published');
