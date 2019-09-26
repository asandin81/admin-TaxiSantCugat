<?php ?>
<form role="form" class="ajax-form">
	<div class="inp"><input type="text" class="required" name="name" id="name" placeholder="Nombre..." /></div>
	<div class="inp"><input type="text" class="required" name="tel" id="tel" placeholder="Telefono..." /></div>
	<div class="inp"><input type="text" class="required" name="email" id="email" placeholder="Email..." /></div>
	<div class="inp"><input type="text" class="required" name="time" id="time" placeholder="Hora y Dia..." /></div>
	<div class="inp"><input type="text" class="required" name="street" id="street" placeholder="Direccion..." /></div>
	<div class="inp"><textarea class="required" name="note" id="note" placeholder="Mensaje..."></textarea></div>
	<button type="submit"></button>
	<div class="spacer"></div>
</form>

<?php ?>
<script type="text/javascript">
	$(document).ready(function(){

		var loader = "<?php echo $loader; ?>";
		
		$('button[type="submit"]').on('click', function(e){
			e.preventDefault();
			
			var errors = 0;
			var alert = $('.alert');
			var $form = $('form.ajax-form');
			
			alert.hide();
			
			$.each($('.required'), function(){
				var $that = $(this);
				if($that.val() == ''){
					$that.addClass('validation_error').parent().append("<div class='alert-no'></div><div class='alert alert-danger'>This field is required.</div>");
					errors++;
				}
			});
			
			if(errors > 0){
				return false;
			} else {
				var serialized = $form.serialize();
				serialized = serialized + "&ajax=1";
				$form.html("<img src='" + loader + "' class='loader' />");
				$.post('', serialized, function(data){
					$('.loader').fadeOut("slow", function(){
						var obj = jQuery.parseJSON(data);
						$form.html(obj.msg);
					});
				});
			}
		});
	});
</script>