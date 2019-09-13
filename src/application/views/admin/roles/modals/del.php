<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title modal-red">删除角色</h4>
</div>
<form method="post" action="/admin/roles/del/<?=$role->id?>" class="ajaxForm">
	<div class="modal-body">
		<div class="boxMessage">
		<?php
		if(isset($message))
			echo $message;
		?>
		</div>
		<input type="hidden" name="id" value="<?=$role->id?>">
		<label class="modal-red">确定要删除这个角色吗？</label>
	</div>

	<div class="modal-footer">
		<input type="submit" class="btn btn-danger submitButton" value="确定" onclick="return submitAjax(1)" <?=isset($message)?"disabled":""?>>
		<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
	</div>
</form>