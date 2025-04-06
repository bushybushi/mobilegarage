Otan o User kami successfully login ginete redirected sto dashboard file.
En ena test file gia emena.
Gia na allaxis to location pu tha gini directed apla emba sto process.php des ta comments j vale to onoma tou homepage File mas


Sto Database tha xriasume episis tuta ta 2 tables 1 gia security questions kai to allo gia reset tokens:


CREATE TABLE IF NOT EXISTS security_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL
);

INSERT INTO security_questions (question) VALUES
('What was your first pet\'s name?'),
('What is your mother\'s maiden name?'),
('What was the name of your first school?'),
('What city were you born in?'),
('What is your favorite book?');


ALTER TABLE users
ADD COLUMN security_question_id INT,
ADD COLUMN security_answer VARCHAR(255),
ADD FOREIGN KEY (security_question_id) REFERENCES security_questions(id);


CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expiry DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(username)
); 
