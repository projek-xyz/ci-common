<!DOCTYPE html>
<html lang="<?php echo $this->e($lang) ?>">
<head>
    <meta charset="<?php echo $this->e($charset) ?>">
    <title><?php echo $this->e($page_name) ?></title>

    <?php echo $this->insert('common::shared/favicons') ?>

    <link rel="stylesheet" href="<?php echo base_url('asset/styles/main.css') ?>">
</head>
<body>

<?php echo $this->section('content') ?>

</body>
</html>
