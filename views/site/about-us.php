<?php
	use yii\helpers\Url;
	$this->title = "Biz haqimizda";
	$this->registerCssFile(Url::base()."/css/about-us.css");
?>
<div class="profile-card">
    <div class="avatar"><img src="<?=Url::base()?>/images/about-me.jpg" style="width: 100px; height: 100px; border-radius: 50px;"></div>
    <div class="founder-name">Safarov Azizbek</div>
    <div class="founder-role">Devstart — platformasi asoschisi</div>

    <p class="info-line"><strong>Email:</strong> azizbek250607@gmail.com</p>
    <p class="info-line"><strong>Telefon:</strong> +998 (77) 003-23-70</p>

    <div class="social-icons">
      <a href="#" title="Telegram">
        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13"></path><path d="M22 2L15 22L11 13L2 9L22 2Z"></path></svg>
      </a>
      <a href="#" title="Instagram">
        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="3"></circle></svg>
      </a>
      <a href="#" title="LinkedIn">
        <svg fill="none" stroke-width="2" viewBox="0 0 24 24"><path d="M16 8C18.209 8 20 9.791 20 12V20H16V12C16 11.4696 15.7893 10.9609 15.4142 10.5858C15.0391 10.2107 14.5304 10 14 10C13.4696 10 12.9609 10.2107 12.5858 10.5858C12.2107 10.9609 12 11.4696 12 12V20H8V8H12V9.2"></path><rect x="4" y="8" width="4" height="12"></rect><circle cx="6" cy="5" r="2"></circle></svg>
      </a>
	</div>
</div>