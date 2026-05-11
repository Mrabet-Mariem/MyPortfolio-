-- ============================================
-- Portfolio Database - Mariem Mrabet
-- ============================================

CREATE DATABASE IF NOT EXISTS `portfolio_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `portfolio_db`;

-- -----------------------------------------------
-- Table: admins
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: admin / Admin@1234
INSERT INTO `admins` (`username`, `password`) VALUES
('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- -----------------------------------------------
-- Table: projects
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
  `id`          INT           NOT NULL AUTO_INCREMENT,
  `title_en`    VARCHAR(200)  NOT NULL,
  `title_fr`    VARCHAR(200)  NOT NULL,
  `title_ar`    VARCHAR(200)  NOT NULL,
  `title_tr`    VARCHAR(200)  NOT NULL,
  `desc_en`     TEXT          NOT NULL,
  `desc_fr`     TEXT          NOT NULL,
  `desc_ar`     TEXT          NOT NULL,
  `desc_tr`     TEXT          NOT NULL,
  `tech`        VARCHAR(255)  NOT NULL,
  `github_url`  VARCHAR(255)  DEFAULT NULL,
  `live_url`    VARCHAR(255)  DEFAULT NULL,
  `image`       VARCHAR(255)  DEFAULT 'assets/img/project-default.png',
  `created_at`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `projects`
  (`title_en`, `title_fr`, `title_ar`, `title_tr`,
   `desc_en`,  `desc_fr`,  `desc_ar`,  `desc_tr`,
   `tech`, `github_url`, `live_url`)
VALUES
(
  'Smart Food Pick-up System',
  'Système de commande de repas maison',
  'نظام طلب وتوزيع الوجبات المنزلية',
  'Akıllı Yemek Sipariş Sistemi',
  'A meal order management system with complete process tracking built with MySQL.',
  'Développement d\'un système de gestion des commandes de repas avec suivi du processus complet.',
  'تطوير نظام لإدارة طلبات الوجبات مع تتبع العملية الكاملة باستخدام MySQL.',
  'MySQL ile tam süreç takibi içeren bir yemek sipariş yönetim sistemi.',
  'MySQL, PHP',
  'https://github.com/mariemmrabet',
  NULL
),
(
  'Machine Learning & Visual Programming',
  'Programmation visuelle et machine learning',
  'البرمجة المرئية والتعلم الآلي',
  'Görsel Programlama ve Makine Öğrenmesi',
  'Dataset analysis and processing with machine learning steps using Python and visual programming.',
  'Analyse et traitement d\'un dataset avec application des étapes de machine learning.',
  'تحليل ومعالجة مجموعة بيانات مع تطبيق خطوات التعلم الآلي.',
  'Python ile veri seti analizi ve işleme ve makine öğrenmesi adımlarının uygulanması.',
  'Python, Machine Learning',
  'https://github.com/mariemmrabet',
  NULL
),
(
  'Inventory Management System',
  'Système de gestion de stock',
  'نظام إدارة المخزون',
  'Stok Yönetim Sistemi',
  'Design of a relational database for inventory management using MySQL and SQL.',
  'Conception d\'une base de données relationnelle pour la gestion des stocks.',
  'تصميم قاعدة بيانات علائقية لإدارة المخزون باستخدام MySQL وSQL.',
  'MySQL ve SQL kullanarak envanter yönetimi için ilişkisel veritabanı tasarımı.',
  'MySQL, SQL',
  'https://github.com/mariemmrabet',
  NULL
),
(
  'Personal Portfolio Website',
  'Portfolio personnel',
  'موقع المحفظة الشخصية',
  'Kişisel Portfolyo Web Sitesi',
  'A full-stack web portfolio showcasing projects and technical skills built with HTML, CSS, JavaScript, PHP and MySQL.',
  'Développement d\'un portfolio web pour présenter les projets et compétences techniques.',
  'تطوير محفظة ويب لعرض المشاريع والمهارات التقنية.',
  'Projeleri ve teknik becerileri sergilemek için web portföyü geliştirme.',
  'HTML, CSS, JavaScript, PHP, MySQL',
  'https://github.com/mariemmrabet',
  NULL
);

-- -----------------------------------------------
-- Table: messages
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `messages` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `subject`    VARCHAR(200) NOT NULL,
  `message`    TEXT         NOT NULL,
  `is_read`    TINYINT(1)   DEFAULT 0,
  `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------
-- Table: site_settings (cookies/session prefs)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key`   VARCHAR(100) NOT NULL,
  `setting_value` TEXT         NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('default_language', 'en'),
('dark_mode_default', 'false');
