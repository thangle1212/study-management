<?php
require_once __DIR__ . '/config/env.php';

$variables = [
	'SMTP_HOST',
	'SMTP_PORT',
	'SMTP_USERNAME',
	'SMTP_PASSWORD',
	'SMTP_FROM_EMAIL',
	'SMTP_FROM_NAME',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
	<meta charset="UTF-8">
	<title>Kiểm tra cấu hình SMTP</title>
</head>
<body>
	<h2>Kiểm tra biến môi trường SMTP</h2>
	<ul>
		<?php foreach ($variables as $variable): ?>
			<?php $value = getenv($variable); ?>
			<li>
				<?= htmlspecialchars($variable) ?>:
				<strong><?= $value !== false && $value !== '' ? 'Đã nhận' : 'Chưa nhận' ?></strong>
				<?php if ($variable !== 'SMTP_PASSWORD' && $value !== false && $value !== ''): ?>
					(<?= htmlspecialchars($variable === 'SMTP_USERNAME' || $variable === 'SMTP_FROM_EMAIL' ? 'đã có giá trị' : $value) ?>)
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<p>Xóa file <code>test_env.php</code> sau khi kiểm tra xong.</p>
</body>
</html>