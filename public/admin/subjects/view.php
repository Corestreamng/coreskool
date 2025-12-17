<?php
require_once dirname(dirname(dirname(__DIR__))) . '/config/config.php';
requireAuth();
requireRole('admin');
setFlash('info', 'Subject view page - under construction');
redirect('admin/subjects/index.php');
?>
