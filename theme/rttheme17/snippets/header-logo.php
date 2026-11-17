<?php /* Labdoo: logo fijo y enlace del logo al dashboard de edoovillages según el idioma. Sustituye la asignación de $logo_url en la cabecera. */ ?>

			<?php
				global $logo_container;
				$logo_container = get_option(THEMESLUG.'_logo_container');
		#		$logo_url       =$logo_url       = get_option('rttheme_logo_url');
        $logo_url       ="https://www.labdoo.org/wp-content/uploads/2021/02/logo-2-alpha-bar-1-1024x108.jpg";
				$platform_dasb= "https://platform.labdoo.org/content/edoovillages-dashboard";
				if (substr( get_locale(), 0, 2 ) === "es") {
				$platform_dasb= "https://platform.labdoo.org/es/content/edoovillages-dashboard";
				}
				elseif (substr( get_locale(), 0, 2 ) === "en" ) {
                                $platform_dasb= "https://platform.labdoo.org/content/edoovillages-dashboard";
                                }
				elseif (substr( get_locale(), 0, 2 ) === "de") {
                                $platform_dasb= "https://platform.labdoo.org/de/content/edoovillages-dashboard";
                                }
				elseif (substr( get_locale(), 0, 2 ) === "ca") {
                                $platform_dasb= "https://platform.labdoo.org/ca/content/edoovillages-dashboard";
				}
				elseif (substr( get_locale(), 0, 2 ) === "fr") {
                                $platform_dasb= "https://platform.labdoo.org/fr/content/edoovillages-dashboard";
                                }
                                elseif (substr( get_locale(), 0, 2 ) === "it") {
                                $platform_dasb= "https://platform.labdoo.org/it/content/edoovillages-dashboard";
                                }

