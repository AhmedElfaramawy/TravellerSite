-- إضافة مستخدم جديد بدور المسؤول
-- كلمة المرور هي: admin123
-- تم تشفيرها باستخدام password_hash() في PHP

-- التحقق أولاً مما إذا كان المستخدم موجودًا بالفعل
DELETE FROM users WHERE email = 'admin@example.com';

-- إضافة المستخدم الجديد
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@example.com', '$2y$10$UpMQCcir.eCCpPvEMpOwtOQbttSa7f8vWcHUJCCCRxgMYKU1/CFIO', 'admin');

-- رسالة تأكيد
SELECT 'تم إضافة حساب المسؤول بنجاح. البريد الإلكتروني: admin@example.com، كلمة المرور: admin123' AS message;
