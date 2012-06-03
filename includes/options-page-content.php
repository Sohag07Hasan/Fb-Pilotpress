<div class="wrap">
	<h2>Facebook Application </h2>
	<?php 
		if($_POST['fb-app-submission'] =="Y"){
			echo '<div class="updated"><p>saved</p></div>';
		}
	?>
	<form class="form-table" method="post" action="">
		<input type="hidden" name="fb-app-submission" value="Y" />
		<table>
			<tr>
				<th>Facebook Application Id</th>
				<td><input type="text" size="40" name="fb-app-id" value="<?php echo $fb_info['id']; ?>"></td>
			</tr>
			<tr>
				<th>Facebook Application Secret</th>
				<td><input type="text" size="40" name="fb-app-secret" value="<?php echo $fb_info['secret']; ?>"></td>
			</tr>
			<tr>
				<td>
					<input class="button-primary" type="submit" value="save">
				</td>
			</tr>
		</table>
	</form>
</div>