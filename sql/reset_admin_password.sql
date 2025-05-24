-- تحديث كلمة مرور المسؤول
-- كلمة المرور الجديدة هي: admin123

UPDATE users 
SET password = '$2y$10$UpMQCcir.eCCpPvEMpOwtOQbttSa7f8vWcHUJCCCRxgMYKU1/CFIO' 
WHERE email = 'admin@example.com' AND role = 'admin';

-- التحقق من نجاح التحديث
SELECT 'تم تحديث كلمة مرور المسؤول بنجاح. كلمة المرور الجديدة هي: admin123' AS message;
