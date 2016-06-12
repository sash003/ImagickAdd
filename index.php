<?php
header ("Content-Type:text/html; charset=UTF-8", false);
?>

<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
input{
	font-weight: bold;
	font-size: 22px;
	margin-left: 33px;
}
</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script>
	$(function(){

	$('#my_form').on('submit', function(e){
		e.preventDefault();
        $('#buttupload').prop('disabled', true);
		var $that = $(this),
				formData = new FormData($that.get(0)); // создаем новый экземпляр объекта и передаем ему нашу форму
		
		$.ajax({
			url: $that.attr('action'),
			type: $that.attr('method'),
			contentType: false, // важно - убираем форматирование данных по умолчанию
			processData: false, // важно - убираем преобразование строк по умолчанию
			data: formData,
			//dataType: 'json',
			success: function(json){
				if(json){
                    console.log(json)
					$('#files').html(json);
                    $('#buttupload').prop('disabled', false);
				}
			}
		});
	});
});
</script>
</head>
<body>

<div class="main" style="padding: 33px; font-family:'C3216tbU';">

<form  action="magick.php"   method="post" id="my_form" enctype='multipart/form-data'>
<table>
<tr> <td><strong>Выбери jpg, jpeg, png или gif файл:</strong></td><td><input type='file' name='image' /></td></tr>
<tr><td><strong>Теперь картинку водяного</strong></td> <td><input type='file' name='waterimage' /></td></tr>
<tr><td colspan="2"><input type='submit' id='buttupload' value='Вперёд!' /></td></tr>
</table>
</form>

Можно даже гифку на гифку, если сервер не подвиснет )
<br><br>
<div id='files' style="padding: 0 11px;">

</div>
</div>
</body></html>
