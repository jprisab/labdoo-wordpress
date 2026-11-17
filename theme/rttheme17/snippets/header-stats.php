<?php /* Labdoo: barra «Global Progress». Va dentro del bloque de idiomas de la cabecera (en el original, sustituye el contenido de #wpml_flags). Lee data.json, que sube la plataforma Drupal. */ ?>
	<?php	if (substr( get_locale(), 0, 2 ) === "es"):?>
	

<?php echo do_shortcode('[jsoncontentimporter url="https://www.labdoo.org/wp-content/themes/rttheme17/data.json"   basenode="level1"]
	<div style="padding:10px;">
		<div style="display: flex; margin: auto; width: 100%; justify-content: center;">
			<div  property="content:encoded" style="text-decoration: none;font-size: 15px;line-height: 15px;font-family: ExoRegular;">Progreso Global:
				<span class="numbers"><a href="https://platform.labdoo.org/content/dootronics-dashboard" style="color: #ff6309; text-decoration: none;font-size: 20px;line-height: 15px;font-family: ExoRegular;">{dootronics}</a></span>
				<span class="numbers"><a href="https://platform.labdoo.org/content/dootronics-dashboard" style="color: #333333; text-decoration: none;">Dispositivos</a></span>&nbsp; &nbsp;
				<span class="numbers""><a href="https://platform.labdoo.org/content/edoovillages-dashboard" style="color: #ff6309; text-decoration: none;font-size: 20px;line-height: 15px;font-family: ExoRegular;">{escuelas}</a></span>
				<span class="numbers""><a href="https://platform.labdoo.org/content/edoovillages-dashboard" style="color: #333333; text-decoration: none;">Escuelas</a></span>&nbsp; &nbsp;
				<span class="numbers"><a href="https://platform.labdoo.org/content/edoovillages-dashboard" style="color: #ff6309; text-decoration: none;font-size: 20px; line-height: 15px; font-family: ExoRegular;">{estudiantes}</a></span>
				<span class="numbers""><a href="https://platform.labdoo.org/content/edoovillages-dashboard" style="color: #333333; text-decoration: none;">Estudiantes</a></span>&nbsp; &nbsp;
				<span class="numbers""><a href="https://platform.labdoo.org/content/hubs-dashboard" style="color: #ff6309; text-decoration: none;font-size: 20px; line-height: 15px; font-family: ExoRegular;">{hubs}</a></span>
				<span class="numbers"><a href="https://platform.labdoo.org/content/hubs-dashboard" style="color: #333333; text-decoration: none;">Hubs</a></span>&nbsp; &nbsp;
				<span class="numbers"><a href="https://platform.labdoo.org/content/dootronics-dashboard" style="color: #ff6309; text-decoration: none;font-size: 20px;line-height: 15px;font-family: ExoRegular;">{paises}</a></span>
				<span class="numbers"><a href="https://platform.labdoo.org/content/dootronics-dashboard" style="color: #333333; text-decoration: none;">Países</a></span>&nbsp;
                                <span class="numbers"><a href="https://platform.labdoo.org/content/dootronics-dashboard" style="color: #ff6309; text-decoration: none;font-size: 20px;line-height: 15px;font-family: ExoRegular;">{co2saved}</a></span>
                                <span class="numbers"><a href="https://platform.labdoo.org/content/dootronics-dashboard" style="color: #333333; text-decoration: none;">Kg CO2 ahorrados</a></span>&nbsp;
			</div>
		</div>
	</div>
[/jsoncontentimporter]');?>


<?php	elseif (substr( get_locale(), 0, 2 ) === "en"):?>
	<?php echo do_shortcode('[jsoncontentimporter url="https://www.labdoo.org/wp-content/themes/rttheme17/data.json"   basenode="level1"]

<div style="padding:10px;">
	<div style="display: flex; margin: auto; width: 100%; justify-content: center;">
		<div  property="content:encoded"  style="color: #333333; text-decoration: none;">Global Progress:
			<span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{dootronics}</a></span>
			<span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Devices</a></span>&nbsp; &nbsp;
			<span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/edoovillages-dashboard" >{escuelas}</a></span>
			<span ><a class="numbers" href="https://platform.labdoo.org/content/edoovillages-dashboard" >Schools</a></span>&nbsp; &nbsp;
			<span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/edoovillages-dashboard" >{estudiantes}</a></span>
			<span ><a class="numbers" href="https://platform.labdoo.org/content/edoovillages-dashboard">Students</a></span>&nbsp; &nbsp;
			<span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/hubs-dashboard" >{hubs}</a></span>
			<span ><a class="numbers" href="https://platform.labdoo.org/content/hubs-dashboard">Hubs</a></span>&nbsp; &nbsp;
			<span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{paises}</a></span>
			<span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Countries</a></span>&nbsp;
			<span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{co2saved}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Kgs CO2 saved</a></span>&nbsp;

		</div>

	</div>

</div>

[/jsoncontentimporter]');?>
		

<?php	elseif (substr( get_locale(), 0, 2 ) === "de"):?>
	<?php echo do_shortcode('[jsoncontentimporter url="https://www.labdoo.org/wp-content/themes/rttheme17/data.json"   basenode="level1"]

<div style="padding:10px;">
	<div style="display: flex; margin: auto; width: 100%; justify-content: center;">
		<div  property="content:encoded"  style="color: #333333; text-decoration: none;">Projektstand:
			<span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{dootronics}</a></span>
			<span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >IT-Spenden</a></span>&nbsp; &nbsp;
			<span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/edoovillages-dashboard" >{escuelas}</a></span>
			<span ><a class="numbers" href="https://platform.labdoo.org/content/edoovillages-dashboard" >Projekte</a></span>&nbsp; &nbsp;
			<span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/edoovillages-dashboard" >{estudiantes}</a></span>
			<span ><a class="numbers" href="https://platform.labdoo.org/content/edoovillages-dashboard">SchülerInnen</a></span>&nbsp; &nbsp;
			<span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/hubs-dashboard" >{hubs}</a></span>
			<span ><a class="numbers" href="https://platform.labdoo.org/content/hubs-dashboard">Hubs</a></span>&nbsp; &nbsp;
			<span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{paises}</a></span>
			<span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Länder</a></span>&nbsp;
	                <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{co2saved}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Kgs CO2 eingespart</a></span>&nbsp;

		</div>

	</div>

</div>

[/jsoncontentimporter]');?>

<?php   elseif (substr( get_locale(), 0, 2 ) === "ca"):?>
        <?php echo do_shortcode('[jsoncontentimporter url="https://www.labdoo.org/wp-content/themes/rttheme17/data.json"   basenode="level1"]

<div style="padding:10px;">
        <div style="display: flex; margin: auto; width: 100%; justify-content: center;">
                <div  property="content:encoded"  style="color: #333333; text-decoration: none;">Progrés Global:
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{dootronics}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Dispositius</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/edoovillages-dashboard" >{escuelas}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/edoovillages-dashboard" >Escoles</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/edoovillages-dashboard" >{estudiantes}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/edoovillages-dashboard">Estudiants</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/hubs-dashboard" >{hubs}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/hubs-dashboard">Hubs</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{paises}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Països</a></span>&nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{co2saved}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Kgs CO2 estalviats</a></span>&nbsp;

                </div>

        </div>

</div>

[/jsoncontentimporter]');?>

<?php   elseif (substr( get_locale(), 0, 2 ) === "fr"):?>
        <?php echo do_shortcode('[jsoncontentimporter url="https://www.labdoo.org/wp-content/themes/rttheme17/data.json"   basenode="level1"]

<div style="padding:10px;">
        <div style="display: flex; margin: auto; width: 100%; justify-content: center;">
                <div  property="content:encoded"  style="color: #333333; text-decoration: none;">Évolution globale:
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{dootronics}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Appareils</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/edoovillages-dashboard" >{escuelas}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/edoovillages-dashboard" >Écoles</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/edoovillages-dashboard" >{estudiantes}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/edoovillages-dashboard">Élèves</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/hubs-dashboard" >{hubs}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/hubs-dashboard">Hubs</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{paises}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Pays</a></span>&nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{co2saved}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Kgs CO2 économisé</a></span>&nbsp;

                </div>

        </div>

</div>

[/jsoncontentimporter]');?>


<?php   elseif (substr( get_locale(), 0, 2 ) === "it"):?>
        <?php echo do_shortcode('[jsoncontentimporter url="https://www.labdoo.org/wp-content/themes/rttheme17/data.json"   basenode="level1"]

<div style="padding:10px;">
        <div style="display: flex; margin: auto; width: 100%; justify-content: center;">
                <div  property="content:encoded"  style="color: #333333; text-decoration: none;">Progresso globale:
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/it/content/dootronics-dashboard">{dootronics}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/it/content/dootronics-dashboard" >Dispositivi</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/it/content/edoovillages-dashboard" >{escuelas}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/it/content/edoovillages-dashboard" >Scuole</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/it/content/edoovillages-dashboard" >{estudiantes}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/it/content/edoovillages-dashboard">Studenti</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/it/content/hubs-dashboard" >{hubs}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/it/content/hubs-dashboard">Hubs</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/it/content/dootronics-dashboard">{paises}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/it/content/dootronics-dashboard" >Paesi</a></span>&nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/it/content/dootronics-dashboard">{co2saved}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/it/content/dootronics-dashboard" >Kgs CO2 risparmiati</a></span>&nbsp;

                </div>
        </div>

</div>

[/jsoncontentimporter]');?>

<?php   else:?>
        <?php echo do_shortcode('[jsoncontentimporter url="https://www.labdoo.org/wp-content/themes/rttheme17/data.json"   basenode="level1"]

<div style="padding:10px;">
        <div style="display: flex; margin: auto; width: 100%; justify-content: center;">
                 <div  property="content:encoded"  style="color: #333333; text-decoration: none;">Global Progress:
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{dootronics}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Devices</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/edoovillages-dashboard" >{escuelas}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/edoovillages-dashboard" >Schools</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/edoovillages-dashboard" >{estudiantes}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/edoovillages-dashboard">Students</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/hubs-dashboard" >{hubs}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/hubs-dashboard">Hubs</a></span>&nbsp; &nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{paises}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Countries</a></span>&nbsp;
                        <span ><a class="numbers text-orange" href="https://platform.labdoo.org/content/dootronics-dashboard">{co2saved}</a></span>
                        <span ><a class="numbers" href="https://platform.labdoo.org/content/dootronics-dashboard" >Kgs CO2 saved</a></span>&nbsp;

                </div>
        </div>
</div>

[/jsoncontentimporter]');?>

<?php endif;?>
