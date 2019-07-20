<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
</head>
<body>

<?php
// Message
if(isset($response)){
    echo $response;
}
?>

<form method='post' action='<?=base_url('Crud')?>' enctype="multipart/form-data">
    <input type='file' name='file' >
    <input type='submit' value='Upload' name='upload'>
</form>

</body>
</html>