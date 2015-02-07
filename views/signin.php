<div class="formish width75">
	<form method="post">
		<?= $this->errorSummary($errors); ?>
		<div class="shift">
			<p>Введите данные вашей учетной записи на сервисе FormDesigner.ru, под которой Вы хотите авторизоваться.</p>
		</div>
		<div class="shift">
			<label>E-mail адрес <span class="required">*</span></label>
			<div class="unit">
				<input type="text" class="text" name="formdesigner[email]" value="<?php echo esc_attr( $email );?>" />
			</div>
		</div>
		<div class="shift">
			<label>Пароль <span class="required">*</span></label>
			<div class="unit">
				<input type="password" class="text"  name="formdesigner[pass]" value="<?php echo esc_attr( $pass );?>" />
			</div>
		</div>
		<div class="shift btn">
			<button type="submit" class="button blue" name="signin">Войти</button>
			<a class="button" href="https://formdesigner.ru/account/forgotPassword" target="_blank" style="margin-left: 15px;">Забыли пароль?</a>
		</div>
	</form>
</div>