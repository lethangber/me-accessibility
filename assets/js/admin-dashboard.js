/**
 * Admin-Dashboard JavaScript für ME Accessibility.
 *
 * Enthält:
 *  - Tab-Navigation
 *  - WCAG-Scanner AJAX
 *  - Barrierefreiheitserklärung-Generator
 *  - WordPress Mediathek-Picker für das Admin-Logo
 */
jQuery(document).ready(function($) {
	'use strict';

	// ==========================================
	// 1. TAB-NAVIGATION
	// ==========================================
	function initTabs() {
		var hash = window.location.hash;
		if (hash) {
			var activeTab = $('.me-tabs a[href="' + hash + '"]');
			if (activeTab.length) {
				switchTab(activeTab.attr('data-tab'));
			}
		}

		$('.me-tabs a').on('click', function(e) {
			e.preventDefault();
			var tabId = $(this).attr('data-tab');
			window.location.hash = $(this).attr('href');
			switchTab(tabId);
		});

		$('.me-tab-trigger').on('click', function(e) {
			e.preventDefault();
			var tabId = $(this).attr('data-tab');
			var targetHref = $(this).attr('href');
			window.location.hash = targetHref;
			switchTab(tabId);
		});
	}

	function switchTab(tabId) {
		$('.me-tabs a').removeClass('nav-tab-active');
		$('.me-tabs a[data-tab="' + tabId + '"]').addClass('nav-tab-active');

		$('.me-tab-content').removeClass('me-active-content');
		$('#tab-' + tabId).addClass('me-active-content');
	}

	initTabs();

	// ==========================================
	// 2. WCAG CRAWLER / SCANNER AJAX
	// ==========================================
	$('.me-trigger-scan-btn').on('click', function(e) {
		e.preventDefault();
		
		var $btn = $(this);
		var $progressArea = $('.me-scanner-progress');
		var $progressBar = $('.me-progress-bar');
		var $progressStatus = $('.me-progress-status');

		$btn.prop('disabled', true);
		$progressArea.slideDown();
		$progressBar.css('width', '10%');
		$progressStatus.text(meAccessibilityAdmin.translations.scanning);

		// AJAX-Lauf zum Starten des Scans.
		$.ajax({
			url: meAccessibilityAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'me_start_wcag_scan',
				nonce: meAccessibilityAdmin.scannerNonce
			},
			success: function(response) {
				if (response.success && response.data.scan_id) {
					// Lade-Status simulieren/hochzählen
					simulateScanProgress(response.data.scan_id);
				} else {
					scanFailed($btn, $progressArea, $progressStatus);
				}
			},
			error: function() {
				scanFailed($btn, $progressArea, $progressStatus);
			}
		});
	});

	function simulateScanProgress(scanId) {
		var $progressBar = $('.me-progress-bar');
		var $progressStatus = $('.me-progress-status');
		var progress = 10;

		var interval = setInterval(function() {
			progress += Math.floor(Math.random() * 15) + 5;
			if (progress >= 95) {
				progress = 95;
				clearInterval(interval);
				
				// Finaler AJAX-Abschluss-Aufruf.
				completeScan(scanId);
			}
			$progressBar.css('width', progress + '%');
			$progressStatus.text('Analysiere Seitenkompatibilität... ' + progress + '%');
		}, 300);
	}

	function completeScan(scanId) {
		var $progressBar = $('.me-progress-bar');
		var $progressStatus = $('.me-progress-status');
		var $btn = $('.me-trigger-scan-btn');

		$.ajax({
			url: meAccessibilityAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'me_complete_wcag_scan',
				scan_id: scanId,
				nonce: meAccessibilityAdmin.scannerNonce
			},
			success: function(response) {
				$progressBar.css('width', '100%');
				$progressStatus.text(meAccessibilityAdmin.translations.scanCompleted);
				
				setTimeout(function() {
					window.location.reload();
				}, 1000);
			},
			error: function() {
				scanFailed($btn, $('.me-scanner-progress'), $progressStatus);
			}
		});
	}

	function scanFailed($btn, $progressArea, $progressStatus) {
		$progressStatus.text(meAccessibilityAdmin.translations.scanFailed);
		setTimeout(function() {
			$progressArea.slideUp();
			$btn.prop('disabled', false);
		}, 3000);
	}

	// Verlauf löschen
	$('.me-clear-history-btn').on('click', function(e) {
		e.preventDefault();
		if (confirm(meAccessibilityAdmin.translations.confirmHistory)) {
			$.ajax({
				url: meAccessibilityAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'me_clear_scan_history',
					nonce: meAccessibilityAdmin.scannerNonce
				},
				success: function() {
					window.location.reload();
				}
			});
		}
	});


	// ==========================================
	// 3. BARRIEREFREIHEITSERKLÄRUNG GENERATOR (Redesigned)
	// ==========================================
	function getTemplates(companyName) { return {
			de: {
				title: 'Barrierefreiheitserklärung für ' + companyName,
				intro: 'Bei <strong>' + companyName + '</strong> setzen wir uns dafür ein, unsere Website und digitalen Inhalte möglichst barrierefrei zugänglich zu gestalten. Unser Ziel ist es, allen Menschen einen gleichberechtigten und uneingeschränkten Zugang zu den bereitgestellten Informationen und Dienstleistungen zu ermöglichen, unabhängig von individuellen Fähigkeiten oder eingesetzten Technologien.',
				appliesTo: 'Diese Erklärung zur Barrierefreiheit gilt für:',
				statusTitle: 'Stand der Vereinbarkeit mit den Anforderungen',
				statusIntro: 'Wir orientieren uns an den Anforderungen der:',
				wcag: 'Web Content Accessibility Guidelines (WCAG) 2.1, Konformitätsstufe AA',
				bitv: 'Barrierefreie-Informationstechnik-Verordnung (BITV 2.0)',
				en301: 'EN 301 549',
				statusPartly: 'Die Website ist derzeit <strong>teilweise</strong> mit den genannten Anforderungen vereinbar. Wir arbeiten kontinuierlich daran, bestehende Barrieren zu identifizieren und schrittweise zu reduzieren.',
				measuresTitle: 'Verwendete Maßnahmen zur Barrierefreiheit',
				measuresIntro: 'Zur Verbesserung der digitalen Barrierefreiheit nutzt diese Website verschiedene technische und visuelle Unterstützungsfunktionen. Dazu gehören unter anderem:',
				measures: [
					'Anpassbare Schriftgrößen',
					'Kontrast- und Farbanpassungen',
					'Tastaturnavigation',
					'Hervorhebung von Links und Überschriften',
					'Fokus-Unterstützung für Tastaturbedienung',
					'Lesefunktionen mittels Browser-Sprachausgabe',
					'Unterstützung für Screenreader',
					'Responsive und skalierbare Darstellung',
					'Accessibility-Toolbar mit erweiterten Bedienhilfen'
				],
				shortcutIntro: 'Die Accessibility-Toolbar kann über die Oberfläche der Website geöffnet werden. Zusätzlich steht eine Schnellzugriff-Tastenkombination zur Verfügung:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Technische Hinweise',
				techDesc: 'Die Vorlesefunktion dieser Website verwendet die native SpeechSynthesis-Technologie des jeweiligen Browsers. Die Verfügbarkeit einzelner Stimmen und Sprachoptionen hängt vom verwendeten Gerät, Betriebssystem und Browser ab.',
				limitationsTitle: 'Bekannte Einschränkungen',
				limitationsIntro: 'Trotz unserer Bemühungen können einzelne Inhalte oder Funktionen vorübergehend noch nicht vollständig barrierefrei sein. Dies betrifft insbesondere:',
				limitations: [
					'ältere PDF-Dokumente',
					'eingebundene Inhalte externer Anbieter',
					'Drittanbieter-Widgets oder Kartenmaterial',
					'einzelne ältere Seitenbereiche'
				],
				limitationsOutro: 'Wir arbeiten kontinuierlich an Verbesserungen und technischen Optimierungen.',
				checkingTitle: 'Prüfung der Barrierefreiheit',
				checkingIntro: 'Die Überprüfung der Website erfolgt regelmäßig durch:',
				checking: [
					'automatisierte Accessibility-Scans',
					'manuelle Funktionstests',
					'Tastatur-Navigationstests',
					'technische Prüfungen auf WCAG-relevante Probleme'
				],
				contactTitle: 'Feedback und Kontakt',
				contactIntro: 'Wenn Ihnen Barrieren auf unserer Website auffallen oder Sie Informationen zu nicht barrierefreien Inhalten benötigen, kontaktieren Sie uns bitte.',
				emailLabel: 'E-Mail:',
				contactOutro: 'Wir bemühen uns, Ihre Anfrage innerhalb von 3 bis 5 Werktagen zu bearbeiten.',
				settlementTitle: 'Schlichtungsverfahren',
				settlementDesc: 'Falls Sie der Ansicht sind, durch eine nicht ausreichende barrierefreie Gestaltung benachteiligt zu sein und Ihre Anfrage nicht zufriedenstellend beantwortet wurde, können Sie sich an die zuständige Schlichtungsstelle wenden.',
				alertConsent: 'Bitte bestätigen Sie die Haftungsausschlusserklärung vor dem Generieren.',
				lastUpdate: 'Letzte Aktualisierung:'
			},
			en: {
				title: 'Accessibility Statement for ' + companyName,
				intro: 'At <strong>' + companyName + '</strong>, we are committed to making our website and digital content as accessible as possible. Our goal is to provide equal and unrestricted access to the information and services provided for everyone, regardless of individual abilities or technologies used.',
				appliesTo: 'This accessibility statement applies to:',
				statusTitle: 'Compliance Status',
				statusIntro: 'We align with the requirements of:',
				wcag: 'Web Content Accessibility Guidelines (WCAG) 2.1, Conformance Level AA',
				bitv: 'European Accessibility Act (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'The website is currently <strong>partially</strong> compliant with the mentioned requirements. We are continuously working on identifying and step-by-step reducing existing barriers.',
				measuresTitle: 'Accessibility Measures Taken',
				measuresIntro: 'To improve digital accessibility, this website utilizes various technical and visual support features. These include:',
				measures: [
					'Adjustable font sizes',
					'Contrast and color adjustments',
					'Keyboard navigation',
					'Highlighting of links and headings',
					'Focus indicators for keyboard usage',
					'Reading features using browser text-to-speech',
					'Support for screen readers',
					'Responsive and scalable presentation',
					'Accessibility toolbar with advanced controls'
				],
				shortcutIntro: 'The accessibility toolbar can be opened via the website interface. In addition, a quick access keyboard shortcut is available:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Technical Notes',
				techDesc: 'The reading feature of this website uses the native SpeechSynthesis technology of the respective browser. The availability of individual voices and language options depends on the device, operating system, and browser used.',
				limitationsTitle: 'Known Limitations',
				limitationsIntro: 'Despite our efforts, individual content or features may temporarily not be fully accessible. This applies in particular to:',
				limitations: [
					'Older PDF documents',
					'Embedded content from external providers',
					'Third-party widgets or maps',
					'Individual older page sections'
				],
				limitationsOutro: 'We are continuously working on improvements and technical optimizations.',
				checkingTitle: 'Accessibility Verification',
				checkingIntro: 'The website is verified regularly through:',
				checking: [
					'Automated accessibility scans',
					'Manual usability tests',
					'Keyboard navigation tests',
					'Technical reviews of WCAG-relevant issues'
				],
				contactTitle: 'Feedback and Contact',
				contactIntro: 'If you notice any barriers on our website or need information on non-accessible content, please contact us.',
				emailLabel: 'Email:',
				contactOutro: 'We endeavor to respond to your request within 3 to 5 business days.',
				settlementTitle: 'Enforcement Procedure',
				settlementDesc: 'If you believe you have been disadvantaged due to insufficient accessibility design and your request has not been satisfactorily answered, you can contact the responsible enforcement body.',
				alertConsent: 'Please confirm the disclaimer before generating.',
				lastUpdate: 'Last Updated:'
			},
			fr: {
				title: 'Déclaration d\'accessibilité pour ' + companyName,
				intro: 'Chez <strong>' + companyName + '</strong>, nous nous engageons à rendre notre site Web et nos contenus numériques aussi accessibles que possible. Notre objectif est de permettre à tous un accès égal et sans restriction aux informations et services fournis, quelles que soient les capacités individuelles ou les technologies utilisées.',
				appliesTo: 'Cette déclaration d\'accessibilité s\'applique à :',
				statusTitle: 'État de conformité',
				statusIntro: 'Nous nous alignons sur les exigences de :',
				wcag: 'Règles pour l\'accessibilité des contenus Web (WCAG) 2.1, niveau de conformité AA',
				bitv: 'Acte européen sur l\'accessibilité (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Le site Web est actuellement <strong>partiellement</strong> conforme aux exigences mentionnées. Nous travaillons continuellement à identifier et à réduire progressivement les barrières existantes.',
				measuresTitle: 'Mesures d\'accessibilité prises',
				measuresIntro: 'Pour améliorer l\'accessibilité numérique, ce site Web utilise diverses fonctionnalités d\'assistance technique et visuelle. Celles-ci comprennent :',
				measures: [
					'Tailles de police ajustables',
					'Ajustements de contraste et de couleur',
					'Navigation au clavier',
					'Surlignage des liens et des en-têtes',
					'Indicateurs de focus pour l\'utilisation du clavier',
					'Fonctions de lecture utilisant la synthèse vocale du navigateur',
					'Prise en charge des lecteurs d\'écran',
					'Présentation adaptative et évolutive',
					'Barre d\'outils d\'accessibilité avec commandes avancées'
				],
				shortcutIntro: 'La barre d\'outils d\'accessibilité peut être ouverte via l\'interface du site Web. De plus, un raccourci clavier d\'accès rapide est disponible :',
				winShortcut: 'Windows : <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS : <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Notes techniques',
				techDesc: 'La fonction de lecture de ce site Web utilise la technologie native SpeechSynthesis du navigateur respectif. La disponibilité des voix individuelles et des options de langue dépend de l\'appareil, du système d\'exploitation et du navigateur utilisés.',
				limitationsTitle: 'Limitations connues',
				limitationsIntro: 'Malgré nos efforts, certains contenus ou fonctionnalités peuvent temporairement ne pas être entièrement accessibles. Cela s\'applique en particulier aux :',
				limitations: [
					'Documents PDF anciens',
					'Contenus intégrés de prestataires externes',
					'Widgets ou cartes de tiers',
					'Sections de page plus anciennes'
				],
				limitationsOutro: 'Nous travaillons continuellement à des améliorations et à des optimisations techniques.',
				checkingTitle: 'Vérification de l\'accessibilité',
				checkingIntro: 'Le site Web est vérifié régulièrement par :',
				checking: [
					'Analyses d\'accessibilité automatisées',
					'Tests d\'utilisabilité manuels',
					'Tests de navigation au clavier',
					'Examens techniques des problèmes pertinents pour la WCAG'
				],
				contactTitle: 'Feedback et contact',
				contactIntro: 'Si vous remarquez des barrières sur notre site Web ou si vous avez besoin d\'informations sur des contenus non accessibles, veuillez nous contacter.',
				emailLabel: 'E-mail :',
				contactOutro: 'Nous nous efforçons de répondre à votre demande dans un délai de 3 à 5 jours ouvrables.',
				settlementTitle: 'Procédure d\'exécution',
				settlementDesc: 'Si vous estimez avoir été désavantagé en raison d\'une accessibilité insuffisante et que votre demande n\'a pas reçu de réponse satisfaisante, vous pouvez contacter l\'organisme d\'exécution responsable.',
				alertConsent: 'Veuillez confirmer la clause de non-responsabilité avant de générer.',
				lastUpdate: 'Dernière mise à jour :'
			},
			es: {
				title: 'Declaración de accesibilidad para ' + companyName,
				intro: 'En <strong>' + companyName + '</strong>, nos comprometemos a hacer que nuestro sitio web y nuestros contenidos digitales sean lo más accesibles posible. Nuestro objetivo es garantizar un acceso equitativo e ilimitado a la información y los servicios prestados a todas las personas, independientemente de sus capacidades individuales o de las tecnologías utilizadas.',
				appliesTo: 'Esta declaración de accesibilidad se aplica a:',
				statusTitle: 'Grado de compatibilidad',
				statusIntro: 'Nos basamos en los requisitos de:',
				wcag: 'Pautas de Accesibilidad para el Contenido Web (WCAG) 2.1, nivel de conformidad AA',
				bitv: 'Acta Europea de Accesibilidad (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'El sitio web es actualmente <strong>parcialmente</strong> conforme con los requisitos mencionados. Trabajamos continuamente para identificar y reducir progresivamente las barreras existentes.',
				measuresTitle: 'Medidas de accesibilidad adoptadas',
				measuresIntro: 'Para mejorar la accesibilidad digital, este sitio web utiliza varias funciones de asistencia técnica y visual. Estas incluyen:',
				measures: [
					'Tamaños de letra ajustables',
					'Ajustes de contraste y color',
					'Navegación por teclado',
					'Resaltado de enlaces y encabezados',
					'Indicadores de enfoque para el uso del teclado',
					'Funciones de lectura mediante el sintetizador de voz del navegador',
					'Soporte para lectores de pantalla',
					'Presentación adaptativa y escalable',
					'Barra de herramientas de accesibilidad con controles avanzados'
				],
				shortcutIntro: 'La barra de herramientas de accesibilidad se puede abrir a través de la interfaz del sitio web. Además, está disponible un atajo de teclado de acceso rápido:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Notas técnicas',
				techDesc: 'La función de lectura de este sitio web utiliza la tecnología nativa SpeechSynthesis del navegador correspondiente. La disponibilidad de voces individuales y opciones de idioma depende del dispositivo, sistema operativo y navegador utilizados.',
				limitationsTitle: 'Limitaciones conocidas',
				limitationsIntro: 'A pesar de nuestros esfuerzos, es posible que parte del contenido o de las funciones no sean totalmente accesibles temporalmente. Esto se aplica en particular a:',
				limitations: [
					'Documentos PDF antiguos',
					'Contenido incrustado de proveedores externos',
					'Widgets o mapas de terceros',
					'Secciones de página más antiguas'
				],
				limitationsOutro: 'Trabajamos continuamente en mejoras y optimizaciones técnicas.',
				checkingTitle: 'Verificación de accesibilidad',
				checkingIntro: 'El sitio web se verifica regularmente a través de:',
				checking: [
					'Análisis de accesibilidad automatizados',
					'Pruebas de usabilidad manuales',
					'Pruebas de navegación por teclado',
					'Revisiones técnicas de problemas relevantes para la WCAG'
				],
				contactTitle: 'Feedback y contacto',
				contactIntro: 'Si nota alguna barrera en nuestro sitio web o necesita información sobre contenido no accesible, póngase en contacto con nosotros.',
				emailLabel: 'Correo electrónico:',
				contactOutro: 'Nos esforzamos por responder a su solicitud en un plazo de 3 a 5 días hábiles.',
				settlementTitle: 'Procedimiento de aplicación',
				settlementDesc: 'Si cree que ha sido discriminado debido a una accesibilidad insuficiente y su solicitud no ha sido respondida satisfactoriamente, puede ponerse en contacto con el organismo de aplicación responsable.',
				alertConsent: 'Por favor, confirme el aviso de exención de responsabilidad antes de generar.',
				lastUpdate: 'Última actualización:'
			},
			vi: {
				title: 'Tuyên bố về khả năng tiếp cận dành cho ' + companyName,
				intro: 'Tại <strong>' + companyName + '</strong>, chúng tôi cam kết thiết kế trang web và nội dung kỹ thuật số của mình sao cho dễ tiếp cận nhất có thể. Mục tiêu của chúng tôi là cung cấp quyền truy cập bình đẳng và không hạn chế vào thông tin và dịch vụ được cung cấp cho mọi người, không phụ thuộc vào khả năng cá nhân hoặc công nghệ sử dụng.',
				appliesTo: 'Tuyên bố về khả năng tiếp cận này áp dụng cho:',
				statusTitle: 'Mức độ tương thích với các yêu cầu',
				statusIntro: 'Chúng tôi tuân thủ các yêu cầu của:',
				wcag: 'Hướng dẫn truy cập nội dung web (WCAG) 2.1, Mức độ tuân thủ AA',
				bitv: 'Đạo luật tiếp cận Châu Âu (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Trang web hiện đang tương thích <strong>một phần</strong> với các yêu cầu đã nêu. Chúng tôi liên tục nỗ lực để xác định và từng bước giảm thiểu các rào cản hiện có.',
				measuresTitle: 'Các biện pháp hỗ trợ tiếp cận được áp dụng',
				measuresIntro: 'Để cải thiện khả năng tiếp cận kỹ thuật số, trang web này sử dụng nhiều tính năng hỗ trợ kỹ thuật và hình ảnh khác nhau. Chúng bao gồm:',
				measures: [
					'Có thể điều chỉnh cỡ chữ',
					'Điều chỉnh độ tương phản và màu sắc',
					'Điều hướng bằng bàn phím',
					'Làm nổi bật các liên kết và tiêu đề',
					'Chỉ báo tiêu điểm khi sử dụng bàn phím',
					'Các tính năng đọc sử dụng công cụ đọc văn bản tự động của trình duyệt',
					'Hỗ trợ cho trình đọc màn hình',
					'Hiển thị tương thích và có thể mở rộng',
					'Thanh công cụ tiếp cận với các bộ điều khiển nâng cao'
				],
				shortcutIntro: 'Thanh công cụ tiếp cận có thể được mở thông qua giao diện trang web. Ngoài ra, phím tắt truy cập nhanh cũng được hỗ trợ:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Lưu ý kỹ thuật',
				techDesc: 'Tính năng đọc của trang web này sử dụng công nghệ SpeechSynthesis tích hợp sẵn của trình duyệt tương ứng. Tính khả dụng của từng giọng đọc và tùy chọn ngôn ngữ phụ thuộc vào thiết bị, hệ điều hành và trình duyệt được sử dụng.',
				limitationsTitle: 'Các hạn chế đã biết',
				limitationsIntro: 'Mặc dù có nhiều nỗ lực, một số nội dung hoặc tính năng cụ thể có thể tạm thời chưa hoàn toàn dễ tiếp cận. Điều này áp dụng cụ thể đối với:',
				limitations: [
					'Tài liệu PDF cũ hơn',
					'Nội dung nhúng từ các nhà cung cấp bên ngoài',
					'Các widget hoặc bản đồ của bên thứ ba',
					'Một số phần trang cũ hơn'
				],
				limitationsOutro: 'Chúng tôi liên tục làm việc để cải tiến và tối ưu hóa kỹ thuật.',
				checkingTitle: 'Kiểm tra khả năng tiếp cận',
				checkingIntro: 'Trang web được kiểm tra thường xuyên thông qua:',
				checking: [
					'Quét khả năng tiếp cận tự động',
					'Kiểm tra khả năng sử dụng thủ công',
					'Kiểm tra điều hướng bàn phím',
					'Đánh giá kỹ thuật về các vấn đề liên quan đến WCAG'
				],
				contactTitle: 'Phản hồi và thông tin liên hệ',
				contactIntro: 'Nếu bạn nhận thấy bất kỳ rào cản nào trên trang web của chúng tôi hoặc cần thông tin về nội dung chưa dễ tiếp cận, vui lòng liên hệ với chúng tôi.',
				emailLabel: 'Email:',
				contactOutro: 'Chúng tôi cố gắng phản hồi yêu cầu của bạn trong vòng 3 đến 5 ngày làm việc.',
				settlementTitle: 'Thủ tục thực thi',
				settlementDesc: 'Nếu bạn tin rằng mình đã bị bất lợi do thiết kế khả năng tiếp cận chưa đầy đủ và yêu cầu của bạn chưa được giải đáp thỏa đáng, bạn có thể liên hệ với cơ quan thực thi có thẩm quyền.',
				alertConsent: 'Vui lòng xác nhận tuyên bố miễn trừ trách nhiệm trước khi tạo.',
				lastUpdate: 'Cập nhật lần cuối:'
			},
			it: {
				title: 'Dichiarazione di accessibilità per ' + companyName,
				intro: 'In <strong>' + companyName + '</strong>, ci impegniamo a rendere il nostro sito Web e i contenuti digitali quanto più accessibili possibile. Il nostro obiettivo è garantire a tutti un accesso equo e illimitato alle informazioni e ai servizi forniti, indipendentemente dalle capacità individuali o dalle tecnologie utilizzate.',
				appliesTo: 'La presente dichiarazione di accessibilità si applica a:',
				statusTitle: 'Stato di conformità',
				statusIntro: 'Ci allineiamo ai requisiti di:',
				wcag: 'Linee guida per l\'accessibilità dei contenuti Web (WCAG) 2.1, Livello di conformità AA',
				bitv: 'Atto europeo sull\'accessibilità (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Il sito Web è attualmente <strong>parzialmente</strong> conforme ai requisiti citati. Lavoriamo continuamente per identificare e ridurre progressivamente le barriere esistenti.',
				measuresTitle: 'Misure di accessibilità adottate',
				measuresIntro: 'Per migliorare l\'accessibilità digitale, questo sito Web utilizza varie funzionalità di supporto tecnico e visivo. Queste includono:',
				measures: ['Dimensioni del carattere regolabili','Regolazioni di contrasto e colore','Navigazione da tastiera','Evidenziazione di collegamenti e intestazioni','Indicatori di messa a fuoco per l\'utilizzo da tastiera','Funzioni di lettura tramite sintesi vocale del browser','Supporto per lettori di schermo','Presentazione adattiva e scalabile','Barra degli strumenti di accessibilità con controlli avanzati'],
				shortcutIntro: 'La barra degli strumenti di accessibilità può essere aperta tramite l\'interfaccia del sito Web. È disponibile anche una scorciatoia da tastiera per l\'accesso rapido:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Note tecniche',
				techDesc: 'La funzione di lettura di questo sito Web utilizza la tecnologia nativa SpeechSynthesis del rispettivo browser. La disponibilità di singole voci e opzioni linguistiche dipende dal dispositivo, dal sistema operativo e dal browser utilizzati.',
				limitationsTitle: 'Limitazioni note',
				limitationsIntro: 'Nonostante i nostri sforzi, singoli contenuti o funzionalità potrebbero temporaneamente non essere completamente accessibili. Ciò riguarda in particolare:',
				limitations: ['Documenti PDF più datati','Contenuti incorporati di fornitori esterni','Widget o mappe di terze parti','Singole sezioni di pagina più datate'],
				limitationsOutro: 'Lavoriamo continuamente a miglioramenti e ottimizzazioni tecniche.',
				checkingTitle: 'Verifica dell\'accessibilità',
				checkingIntro: 'Il sito Web viene verificato regolarmente tramite:',
				checking: ['Scansioni automatizzate di accessibilità','Test di usabilità manuali','Test di navigazione da tastiera','Revisioni tecniche di problemi rilevanti per le WCAG'],
				contactTitle: 'Feedback e contatti',
				contactIntro: 'Se noti barriere sul nostro sito Web o hai bisogno di informazioni su contenuti non accessibili, contattaci.',
				emailLabel: 'E-mail:',
				contactOutro: 'Ci impegniamo a rispondere alla tua richiesta entro 3-5 giorni lavorativi.',
				settlementTitle: 'Procedura di esecuzione',
				settlementDesc: 'Se ritieni di essere stato svantaggiato a causa di un design di accessibilità insufficiente e la tua richiesta non ha ricevuto risposta soddisfacente, puoi contattare l\'organismo di esecuzione competente.',
				alertConsent: 'Si prega di confermare la dichiarazione di non responsabilità prima di generare.',
				lastUpdate: 'Ultimo aggiornamento:'
			},
			nl: {
				title: 'Toegankelijkheidsverklaring voor ' + companyName,
				intro: 'Bij <strong>' + companyName + '</strong> streven we ernaar onze website en digitale inhoud zo toegankelijk mogelijk te maken. Ons doel is om voor iedereen gelijke en onbeperkte toegang te bieden tot de verstrekte informatie en diensten, ongeacht individuele capaciteiten of gebruikte technologieën.',
				appliesTo: 'Deze toegankelijkheidsverklaring is van toepassing op:',
				statusTitle: 'Conformiteitsstatus',
				statusIntro: 'Wij houden ons aan de vereisten van:',
				wcag: 'Web Content Accessibility Guidelines (WCAG) 2.1, Conformiteitsniveau AA',
				bitv: 'European Accessibility Act (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'De website voldoet momenteel <strong>gedeeltelijk</strong> aan de genoemde vereisten. We werken continu aan het identificeren en stapsgewijs reduceren van bestaande barrières.',
				measuresTitle: 'Genomen toegankelijkheidsmaatregelen',
				measuresIntro: 'Om de digitale toegankelijkheid te verbeteren, maakt deze website gebruik van verschillende technische en visuele ondersteuningsfuncties. Deze omvatten:',
				measures: ['Instelbare lettergrootten','Contrast- en kleuraanpassingen','Toetsenbordnavigatie','Markering van links en kopjes','Focusindicatoren voor toetsenbordgebruik','Leesfuncties via tekst-naar-spraak van de browser','Ondersteuning voor schermlezers','Responsieve en schaalbare presentatie','Toegankelijkheidswerkbalk met geavanceerde bedieningselementen'],
				shortcutIntro: 'De toegankelijkheidswerkbalk kan worden geopend via de website-interface. Daarnaast is er een sneltoets voor snelle toegang beschikbaar:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Technische opmerkingen',
				techDesc: 'De leesfunctie van deze website maakt gebruik van de native SpeechSynthesis-technologie van de respectievelijke browser. De beschikbaarheid van individuele stemmen en taalopties is afhankelijk van het gebruikte apparaat, besturingssysteem en browser.',
				limitationsTitle: 'Bekende beperkingen',
				limitationsIntro: 'Ondanks onze inspanningen zijn bepaalde inhoud of functies mogelijk tijdelijk niet volledig toegankelijk. Dit geldt met name voor:',
				limitations: ['Oudere PDF-documenten','Ingesloten inhoud van externe aanbieders','Widgets of kaarten van derden','Individuele oudere paginasecties'],
				limitationsOutro: 'We werken voortdurend aan verbeteringen en technische optimalisaties.',
				checkingTitle: 'Toegankelijkheidsverificatie',
				checkingIntro: 'De website wordt regelmatig geverifieerd via:',
				checking: ['Geautomatiseerde toegankelijkheidsscans','Handmatige bruikbaarheidstests','Toetsenbordnavigatietests','Technische reviews van WCAG-relevante problemen'],
				contactTitle: 'Feedback en contact',
				contactIntro: 'Als u barrières op onze website opmerkt of informatie nodig heeft over niet-toegankelijke inhoud, neem dan contact met ons op.',
				emailLabel: 'E-mail:',
				contactOutro: 'We streven ernaar uw verzoek binnen 3 tot 5 werkdagen te beantwoorden.',
				settlementTitle: 'Handhavingsprocedure',
				settlementDesc: 'Als u van mening bent dat u nadeel hebt ondervonden door een onvoldoende toegankelijk ontwerp en uw verzoek niet naar tevredenheid is beantwoord, kunt u contact opnemen met het bevoegde handhavingsorgaan.',
				alertConsent: 'Bevestig de aansprakelijkheidsverklaring voordat u genereert.',
				lastUpdate: 'Laatste update:'
			},
			pl: {
				title: 'Oświadczenie o dostępności dla ' + companyName,
				intro: 'W firmie <strong>' + companyName + '</strong> zobowiązujemy się do jak największej dostępności naszej strony internetowej i treści cyfrowych. Naszym celem jest zapewnienie wszystkim równego i nieograniczonego dostępu do udostępnianych informacji i usług, niezależnie od indywidualnych możliwości lub używanych technologii.',
				appliesTo: 'Niniejsze oświadczenie o dostępności dotyczy:',
				statusTitle: 'Stan zgodności z wymaganiami',
				statusIntro: 'Dostosowujemy się do wymagań:',
				wcag: 'Wytycznych dotyczących dostępności treści internetowych (WCAG) 2.1, poziom zgodności AA',
				bitv: 'Europejskiego aktu o dostępności (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Strona internetowa jest obecnie <strong>częściowo</strong> zgodna z wymienionymi wymaganiami. Stale pracujemy nad identyfikacją i stopniowym ograniczaniem istniejących barier.',
				measuresTitle: 'Zastosowane środki dostępności',
				measuresIntro: 'Aby poprawić dostępność cyfrową, ta strona internetowa wykorzystuje różne funkcje wsparcia technicznego i wizualnego. Należą do nich:',
				measures: ['Możliwość dostosowania rozmiaru czcionki','Regulacja kontrastu i kolorów','Nawigacja klawiaturą','Podświetlanie linków i nagłówków','Wskaźniki skupienia dla obsługi klawiaturą','Funkcje czytania za pomocą czytnika ekranu przeglądarki','Obsługa czytników ekranu','Responsywna i skalowalna prezentacja','Pasek narzędzi dostępności z zaawansowanymi elementami sterującymi'],
				shortcutIntro: 'Pasek narzędzi dostępności można otworzyć za pomocą interfejsu strony internetowej. Dostępny jest również skrót klawiaturowy umożliwiający szybki dostęp:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Uwagi techniczne',
				techDesc: 'Funkcja czytania tej strony internetowej wykorzystuje natywną technologię SpeechSynthesis odpowiedniej przeglądarki. Dostępność poszczególnych głosów i opcji językowych zależy od używanego urządzenia, systemu operacyjnego i przeglądarki.',
				limitationsTitle: 'Znane ograniczenia',
				limitationsIntro: 'Pomimo naszych wysiłków poszczególne treści lub funkcje mogą tymczasowo nie być w pełni dostępne. Dotyczy to w szczególności:',
				limitations: ['Starszych dokumentów PDF','Osadzonych treści zewnętrznych dostawców','Widgetów lub map stron trzecich','Poszczególnych starszych sekcji strony'],
				limitationsOutro: 'Stale pracujemy nad ulepszeniami i optymalizacjami technicznymi.',
				checkingTitle: 'Weryfikacja dostępności',
				checkingIntro: 'Strona internetowa jest regularnie weryfikowana poprzez:',
				checking: ['Zautomatyzowane skanowanie dostępności','Ręczne testy użyteczności','Testy nawigacji klawiaturą','Przeglądy techniczne pod kątem problemów związanych z WCAG'],
				contactTitle: 'Opinie i kontakt',
				contactIntro: 'Jeśli zauważysz bariery na naszej stronie internetowej lub potrzebujesz informacji o niedostępnych treściach, skontaktuj się z nami.',
				emailLabel: 'E-mail:',
				contactOutro: 'Staramy się odpowiedzieć na Twoje zapytanie w ciągu 3 do 5 dni roboczych.',
				settlementTitle: 'Procedura egzekwowania',
				settlementDesc: 'Jeśli uważasz, że zostałeś poszkodowany z powodu niewystarczającej dostępności i Twoje żądanie nie zostało satysfakcjonująco rozpatrzone, możesz skontaktować się z właściwym organem egzekwującym.',
				alertConsent: 'Prosimy potwierdzić oświadczenie o zrzeczeniu się odpowiedzialności przed wygenerowaniem.',
				lastUpdate: 'Ostatnia aktualizacja:'
			},
			pt: {
				title: 'Declaração de acessibilidade para ' + companyName,
				intro: 'Na <strong>' + companyName + '</strong>, comprometemo-nos a tornar o nosso site e conteúdos digitais tão acessíveis quanto possível. O nosso objetivo é proporcionar a todos um acesso igual e irrestrito às informações e serviços prestados, independentemente das capacidades individuais ou das tecnologias utilizadas.',
				appliesTo: 'Esta declaração de acessibilidade aplica-se a:',
				statusTitle: 'Estado de conformidade',
				statusIntro: 'Alinhamo-nos com os requisitos de:',
				wcag: 'Diretrizes de Acessibilidade para Conteúdo da Web (WCAG) 2.1, Nível de Conformidade AA',
				bitv: 'Lei Europeia de Acessibilidade (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'O site está atualmente <strong>parcialmente</strong> em conformidade com os requisitos mencionados. Trabalhamos continuamente para identificar e reduzir progressivamente as barreiras existentes.',
				measuresTitle: 'Medidas de acessibilidade tomadas',
				measuresIntro: 'Para melhorar a acessibilidade digital, este site utiliza diversas funcionalidades de suporte técnico e visual. Estas incluem:',
				measures: ['Tamanhos de fonte ajustáveis','Ajustes de contraste e cor','Navegação por teclado','Destaque de links e títulos','Indicadores de foco para utilização do teclado','Funções de leitura usando texto em fala do navegador','Suporte para leitores de ecrã','Apresentação responsiva e escalável','Barra de ferramentas de acessibilidade com controlos avançados'],
				shortcutIntro: 'A barra de ferramentas de acessibilidade pode ser aberta através da interface do site. Além disso, está disponível um atalho de teclado de acesso rápido:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Notas técnicas',
				techDesc: 'A função de leitura deste site utiliza a tecnologia nativa SpeechSynthesis do respetivo browser. A disponibilidade de vozes individuais e opções de idioma depende do dispositivo, sistema operativo e browser utilizados.',
				limitationsTitle: 'Limitações conhecidas',
				limitationsIntro: 'Apesar dos nossos esforços, conteúdos ou funcionalidades individuais podem temporariamente não estar totalmente acessíveis. Isto aplica-se em particular a:',
				limitations: ['Documentos PDF mais antigos','Conteúdo incorporado de fornecedores externos','Widgets ou mapas de terceiros','Secções de página individuais mais antigas'],
				limitationsOutro: 'Trabalhamos continuamente em melhorias e otimizações técnicas.',
				checkingTitle: 'Verificação da acessibilidade',
				checkingIntro: 'O site é verificado regularmente através de:',
				checking: ['Análises automatizadas de acessibilidade','Testes de usabilidade manuais','Testes de navegação por teclado','Revisões técnicas de questões relevantes para as WCAG'],
				contactTitle: 'Feedback e contacto',
				contactIntro: 'Se notar barreiras no nosso site ou precisar de informações sobre conteúdo não acessível, contacte-nos.',
				emailLabel: 'E-mail:',
				contactOutro: 'Esforçamo-nos por responder ao seu pedido no prazo de 3 a 5 dias úteis.',
				settlementTitle: 'Procedimento de execução',
				settlementDesc: 'Se considerar que foi prejudicado devido a um design de acessibilidade insuficiente e o seu pedido não tiver sido respondido de forma satisfatória, pode contactar o organismo de execução responsável.',
				alertConsent: 'Por favor, confirme a declaração de isenção de responsabilidade antes de gerar.',
				lastUpdate: 'Última atualização:'
			},
			cs: {
				title: 'Prohlášení o přístupnosti pro ' + companyName,
				intro: 'Ve společnosti <strong>' + companyName + '</strong> se zavazujeme k co největší přístupnosti našich webových stránek a digitálního obsahu. Naším cílem je zajistit rovný a neomezený přístup k poskytovaným informacím a službám pro každého, bez ohledu na individuální schopnosti nebo používané technologie.',
				appliesTo: 'Toto prohlášení o přístupnosti se vztahuje na:',
				statusTitle: 'Stav souladu s požadavky',
				statusIntro: 'Řídíme se požadavky:',
				wcag: 'Pokyny pro přístupnost webového obsahu (WCAG) 2.1, úroveň shody AA',
				bitv: 'Evropský zákon o přístupnosti (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Webové stránky jsou v současné době <strong>částečně</strong> v souladu s uvedenými požadavky. Neustále pracujeme na identifikaci a postupném odstraňování stávajících bariér.',
				measuresTitle: 'Přijatá opatření přístupnosti',
				measuresIntro: 'Pro zlepšení digitální přístupnosti využívají tyto webové stránky různé funkce technické a vizuální podpory. Patří mezi ně:',
				measures: ['Nastavitelné velikosti písma','Úpravy kontrastu a barvy','Navigace klávesnicí','Zvýraznění odkazů a nadpisů','Indikátory zaměření pro ovládání klávesnicí','Funkce čtení pomocí převodu textu na řeč v prohlížeči','Podpora pro čtečky obrazovky','Responzivní a škálovatelná prezentace','Panel nástrojů přístupnosti s pokročilými ovládacími prvky'],
				shortcutIntro: 'Panel nástrojů přístupnosti lze otevřít prostřednictvím rozhraní webových stránek. K dispozici je také klávesová zkratka pro rychlý přístup:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Technické poznámky',
				techDesc: 'Funkce čtení těchto webových stránek využívá nativní technologii SpeechSynthesis příslušného prohlížeče. Dostupnost jednotlivých hlasů a jazykových možností závisí na použitém zařízení, operačním systému a prohlížeči.',
				limitationsTitle: 'Známá omezení',
				limitationsIntro: 'I přes naše úsilí nemusí být určitý obsah nebo funkce dočasně plně přístupné. To se týká zejména:',
				limitations: ['Starších dokumentů PDF','Vloženého obsahu od externích poskytovatelů','Widgetů nebo map třetích stran','Jednotlivých starších sekcí stránek'],
				limitationsOutro: 'Neustále pracujeme na vylepšeních a technických optimalizacích.',
				checkingTitle: 'Ověření přístupnosti',
				checkingIntro: 'Webové stránky jsou pravidelně ověřovány prostřednictvím:',
				checking: ['Automatizovaných skenů přístupnosti','Ručních testů použitelnosti','Testů navigace klávesnicí','Technických přezkumů problémů relevantních pro WCAG'],
				contactTitle: 'Zpětná vazba a kontakt',
				contactIntro: 'Pokud na našich webových stránkách zaznamenáte bariéry nebo potřebujete informace o nepřístupném obsahu, kontaktujte nás.',
				emailLabel: 'E-mail:',
				contactOutro: 'Snažíme se odpovědět na váš požadavek do 3 až 5 pracovních dnů.',
				settlementTitle: 'Postup vymáhání',
				settlementDesc: 'Pokud se domníváte, že jste byli znevýhodněni z důvodu nedostatečného bezbariérového designu a váš požadavek nebyl uspokojivě zodpovězen, můžete se obrátit na příslušný orgán pro vymáhání.',
				alertConsent: 'Před vygenerováním potvrďte prosím prohlášení o vyloučení odpovědnosti.',
				lastUpdate: 'Poslední aktualizace:'
			},
			da: {
				title: 'Tilgængelighedserklæring for ' + companyName,
				intro: 'Hos <strong>' + companyName + '</strong> bestræber vi os på at gøre vores hjemmeside og digitale indhold så tilgængeligt som muligt. Vores mål er at give alle lige og ubegrænset adgang til de leverede oplysninger og tjenester, uanset individuelle evner eller brugte teknologier.',
				appliesTo: 'Denne tilgængelighedserklæring gælder for:',
				statusTitle: 'Overensstemmelsesstatus',
				statusIntro: 'Vi tilpasser os kravene i:',
				wcag: 'Retningslinjer for tilgængeligt webindhold (WCAG) 2.1, Overensstemmelsesniveau AA',
				bitv: 'Den europæiske tilgængelighedslov (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Hjemmesiden er i øjeblikket <strong>delvist</strong> i overensstemmelse med de nævnte krav. Vi arbejder løbende på at identificere og gradvist reducere eksisterende barrierer.',
				measuresTitle: 'Trufne tilgængelighedsforanstaltninger',
				measuresIntro: 'For at forbedre den digitale tilgængelighed bruger denne hjemmeside forskellige tekniske og visuelle supportfunktioner. Disse inkluderer:',
				measures: ['Justerbare skriftstørrelser','Kontrast- og farvejusteringer','Tastaturnavigation','Fremhævning af links og overskrifter','Fokusindikatorer til tastaturanvendelse','Læsefunktioner ved hjælp af browserens tekst-til-tale','Understøttelse af skærmlæsere','Responsiv og skalerbar præsentation','Tilgængligheds­værktøjslinje med avancerede kontrolelementer'],
				shortcutIntro: 'Tilgængeligheds­værktøjslinjen kan åbnes via hjemmesidens interface. Derudover er der en tastaturgenvej til hurtig adgang:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Tekniske noter',
				techDesc: 'Læsefunktionen på denne hjemmeside bruger den respektive browsers native SpeechSynthesis-teknologi. Tilgængeligheden af individuelle stemmer og sprogmuligheder afhænger af den brugte enhed, det brugte operativsystem og den brugte browser.',
				limitationsTitle: 'Kendte begrænsninger',
				limitationsIntro: 'På trods af vores bestræbelser kan individuelt indhold eller funktioner midlertidigt ikke være fuldt tilgængeligt. Dette gælder især for:',
				limitations: ['Ældre PDF-dokumenter','Indlejret indhold fra externe udbydere','Tredjeparts-widgets eller kort','Individuelle ældre sideafsnit'],
				limitationsOutro: 'Vi arbejder løbende på forbedringer og tekniske optimeringer.',
				checkingTitle: 'Tilgængeligheds­verifikation',
				checkingIntro: 'Hjemmesiden verificeres regelmæssigt via:',
				checking: ['Automatiserede tilgængeligheds-scans','Manuelle brugbarhedstest','Tastaturnavigationstest','Tekniske gennemgange af WCAG-relevante problemer'],
				contactTitle: 'Feedback og kontakt',
				contactIntro: 'Hvis du bemærker barrierer på vores hjemmeside eller har brug for oplysninger om ikke-tilgængeligt indhold, bedes du kontakte os.',
				emailLabel: 'E-mail:',
				contactOutro: 'Vi bestræber os på at besvare din anmodning inden for 3 til 5 hverdage.',
				settlementTitle: 'Håndhævelsesprocedure',
				settlementDesc: 'Hvis du mener, at du er blevet stillet dårligere på grund af utilstrækkelig tilgængelighed og din anmodning ikke er blevet besvaret tilfredsstillende, kan du kontakte det ansvarlige håndhævelsesorgan.',
				alertConsent: 'Bekræft venligst ansvarsfraskrivelsen, inden du genererer.',
				lastUpdate: 'Sidst opdateret:'
			},
			fi: {
				title: 'Saavutettavuusseloste – ' + companyName,
				intro: 'Me <strong>' + companyName + '</strong>ssä olemme sitoutuneet tekemään verkkosivustostamme ja digitaalisesta sisällöstämme mahdollisimman saavutettavia. Tavoitteemme on tarjota kaikille tasavertainen ja rajoittamaton pääsy tarjottuihin tietoihin ja palveluihin riippumatta yksilöllisistä kyvyistä tai käytetyistä teknologioista.',
				appliesTo: 'Tämä saavutettavuusseloste koskee:',
				statusTitle: 'Vaatimustenmukaisuustila',
				statusIntro: 'Noudatamme seuraavien vaatimuksia:',
				wcag: 'Verkkosisällön saavutettavuusohjeistot (WCAG) 2.1, Vaatimustenmukaisuustaso AA',
				bitv: 'Euroopan saavutettavuuslaki (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Verkkosivusto on tällä hetkellä <strong>osittain</strong> mainittujen vaatimusten mukainen. Työskentelemme jatkuvasti olemassa olevien esteiden tunnistamiseksi ja asteittaiseksi vähentämiseksi.',
				measuresTitle: 'Saavutettavuustoimenpiteet',
				measuresIntro: 'Digitaalisen saavutettavuuden parantamiseksi tämä verkkosivusto käyttää erilaisia teknisiä ja visuaalisia tukitoimintoja. Näihin kuuluvat:',
				measures: ['Säädettävät kirjasinkoot','Kontrasti- ja värisäädöt','Näppäimistönavigointi','Linkkien ja otsikoiden korostaminen','Kohdistusilmaisimet näppäimistökäyttöä varten','Lukutoiminnot selaimen tekstistä puheeksi -tekniikalla','Ruudunlukijoiden tuki','Responsiivinen ja skaalautuva esitys','Saavutettavuustyökalupalkki lisäohjauksilla'],
				shortcutIntro: 'Saavutettavuustyökalupalkki voidaan avata verkkosivuston käyttöliittymän kautta. Lisäksi saatavilla on pikanäppäin nopeaan pääsyyn:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Tekniset huomautukset',
				techDesc: 'Tämän verkkosivuston lukutoiminto käyttää kyseisen selaimen natiivia SpeechSynthesis-teknologiaa. Yksittäisten äänten ja kielivalintojen saatavuus riippuu käytetystä laitteesta, käyttöjärjestelmästä ja selaimesta.',
				limitationsTitle: 'Tunnetut rajoitukset',
				limitationsIntro: 'Ponnisteluistamme huolimatta yksittäiset sisällöt tai ominaisuudet eivät välttämättä ole tilapäisesti täysin saavutettavia. Tämä koskee erityisesti:',
				limitations: ['Vanhempia PDF-asiakirjoja','Ulkoisten tarjoajien upotettua sisältöä','Kolmansien osapuolten widgetejä tai karttoja','Yksittäisiä vanhempia sivun osioita'],
				limitationsOutro: 'Työskentelemme jatkuvasti parannusten ja teknisten optimointien parissa.',
				checkingTitle: 'Saavutettavuuden varmentaminen',
				checkingIntro: 'Verkkosivusto tarkistetaan säännöllisesti seuraavilla tavoilla:',
				checking: ['Automatisoidut saavutettavuusskannaukset','Manuaaliset käytettävyystestit','Näppäimistönavigoinnin testit','WCAG:iin liittyvien ongelmien tekniset katselmukset'],
				contactTitle: 'Palaute ja yhteystiedot',
				contactIntro: 'Jos huomaat verkkosivustollamme esteitä tai tarvitset tietoja saavuttamattomasta sisällöstä, ota meihin yhteyttä.',
				emailLabel: 'Sähköposti:',
				contactOutro: 'Pyrimme vastaamaan pyyntöösi 3–5 arkipäivän kuluessa.',
				settlementTitle: 'Täytäntöönpanomenettely',
				settlementDesc: 'Jos uskot joutuneesi epäedulliseen asemaan riittämättömän saavutettavuussuunnittelun vuoksi eikä pyyntöäsi ole vastattu tyydyttävästi, voit ottaa yhteyttä toimivaltaiseen täytäntöönpanoelimeen.',
				alertConsent: 'Vahvista vastuuvapauslauseke ennen luomista.',
				lastUpdate: 'Viimeksi päivitetty:'
			},
			sv: {
				title: 'Tillgänglighetsredogörelse för ' + companyName,
				intro: 'På <strong>' + companyName + '</strong> strävar vi efter att göra vår webbplats och digitala innehåll så tillgängliga som möjligt. Vårt mål är att ge alla lika och obegränsad tillgång till den tillhandahållna informationen och tjänsterna, oavsett individuella förmågor eller använda tekniker.',
				appliesTo: 'Denna tillgänglighetsredogörelse gäller för:',
				statusTitle: 'Efterlevnadsstatus',
				statusIntro: 'Vi anpassar oss till kraven i:',
				wcag: 'Riktlinjer för tillgängligt webbinnehåll (WCAG) 2.1, Efterlevnadsnivå AA',
				bitv: 'Europeiska tillgänglighetslagen (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Webbplatsen är för närvarande <strong>delvis</strong> i enlighet med de nämnda kraven. Vi arbetar kontinuerligt med att identifiera och stegvis minska befintliga hinder.',
				measuresTitle: 'Vidtagna tillgänglighetsåtgärder',
				measuresIntro: 'För att förbättra den digitala tillgängligheten använder denna webbplats olika tekniska och visuella stödfunktioner. Dessa inkluderar:',
				measures: ['Justerbara teckenstorlekar','Kontrast- och färgjusteringar','Tangentbordsnavigering','Markering av länkar och rubriker','Fokusindikatorer för tangentbordsanvändning','Läsfunktioner med webbläsarens text-till-tal','Stöd för skärmläsare','Responsiv och skalbar presentation','Tillgänglighetsverktygsfält med avancerade kontroller'],
				shortcutIntro: 'Tillgänglighetsverktygsfältet kan öppnas via webbplatsens gränssnitt. Dessutom finns en snabbtangent för snabb åtkomst:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Tekniska noter',
				techDesc: 'Läsfunktionen på denna webbplats använder respektive webbläsares inbyggda SpeechSynthesis-teknik. Tillgängligheten av enskilda röster och språkalternativ beror på den enhet, det operativsystem och den webbläsare som används.',
				limitationsTitle: 'Kända begränsningar',
				limitationsIntro: 'Trots våra ansträngningar kan enskilt innehåll eller funktioner tillfälligt inte vara fullt tillgängliga. Detta gäller i synnerhet:',
				limitations: ['Äldre PDF-dokument','Inbäddat innehåll från externa leverantörer','Tredjeparts­widgetar eller kartor','Enskilda äldre sidavsnitt'],
				limitationsOutro: 'Vi arbetar kontinuerligt med förbättringar och tekniska optimeringar.',
				checkingTitle: 'Tillgänglighetsverifiering',
				checkingIntro: 'Webbplatsen verifieras regelbundet via:',
				checking: ['Automatiserade tillgänglighetsskanningar','Manuella användbarhetstest','Test av tangentbordsnavigering','Tekniska granskningar av WCAG-relevanta problem'],
				contactTitle: 'Feedback och kontakt',
				contactIntro: 'Om du märker hinder på vår webbplats eller behöver information om otillgängligt innehåll, kontakta oss gärna.',
				emailLabel: 'E-post:',
				contactOutro: 'Vi strävar efter att besvara din begäran inom 3 till 5 arbetsdagar.',
				settlementTitle: 'Verkställighetsförfarande',
				settlementDesc: 'Om du anser att du har missgynnats på grund av otillräcklig tillgänglighetsdesign och din begäran inte har besvarats på ett tillfredsställande sätt, kan du kontakta det ansvariga verkställighetsorganet.',
				alertConsent: 'Bekräfta ansvarsfriskrivningen innan du genererar.',
				lastUpdate: 'Senast uppdaterad:'
			},
			hu: {
				title: 'Akadálymentesítési nyilatkozat – ' + companyName,
				intro: 'A <strong>' + companyName + '</strong> vállalatnál elkötelezettük magunkat amellett, hogy weboldalunkat és digitális tartalmainkat a lehető legjobban akadálymentesítsük. Célunk, hogy mindenki számára egyenlő és korlátlan hozzáférést biztosítsunk a nyújtott információkhoz és szolgáltatásokhoz, tekintet nélkül az egyéni képességekre vagy az alkalmazott technológiákra.',
				appliesTo: 'Ez az akadálymentesítési nyilatkozat a következőkre vonatkozik:',
				statusTitle: 'Megfelelőségi állapot',
				statusIntro: 'Igazodunk a következők követelményeihez:',
				wcag: 'Webtartalom-akadálymentesítési irányelvek (WCAG) 2.1, AA megfelelőségi szint',
				bitv: 'Európai Akadálymentesítési Törvény (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'A weboldal jelenleg <strong>részben</strong> megfelel az említett követelményeknek. Folyamatosan dolgozunk a meglévő akadályok azonosításán és fokozatos csökkentésén.',
				measuresTitle: 'Meghozott akadálymentesítési intézkedések',
				measuresIntro: 'A digitális akadálymentesítés javítása érdekében ez a weboldal különböző technikai és vizuális támogatási funkciókat alkalmaz. Ezek a következők:',
				measures: ['Állítható betűméretek','Kontraszt- és színigazítások','Billentyűzetes navigáció','Hivatkozások és fejlécek kiemelése','Fókuszjelzők billentyűzetes használathoz','Olvasási funkciók a böngésző szövegfelolvasójával','Képernyőolvasók támogatása','Reszponzív és skálázható megjelenítés','Akadálymentesítési eszköztár fejlett vezérlőkkel'],
				shortcutIntro: 'Az akadálymentesítési eszköztár a weboldal felületén keresztül nyitható meg. Emellett elérhető egy gyorsbillentyű a gyors hozzáféréshez:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Technikai megjegyzések',
				techDesc: 'Ennek a weboldalnak az olvasási funkciója az adott böngésző natív SpeechSynthesis technológiáját használja. Az egyes hangok és nyelvi opciók elérhetősége a használt eszköztől, operációs rendszertől és böngészőtől függ.',
				limitationsTitle: 'Ismert korlátozások',
				limitationsIntro: 'Erőfeszítéseink ellenére egyes tartalmak vagy funkciók ideiglenesen nem feltétlenül teljes mértékben akadálymentesek. Ez különösen vonatkozik a következőkre:',
				limitations: ['Régebbi PDF-dokumentumok','Külső szolgáltatóktól beágyazott tartalmak','Harmadik féltől származó widgetek vagy térképek','Egyes régebbi oldalszakaszok'],
				limitationsOutro: 'Folyamatosan dolgozunk a fejlesztéseken és a technikai optimalizálásokon.',
				checkingTitle: 'Akadálymentesítési ellenőrzés',
				checkingIntro: 'A weboldal rendszeresen ellenőrzésre kerül a következők révén:',
				checking: ['Automatizált akadálymentesítési vizsgálatok','Manuális használhatósági tesztek','Billentyűzetes navigációs tesztek','WCAG-vonatkozó problémák technikai felülvizsgálatai'],
				contactTitle: 'Visszajelzés és kapcsolat',
				contactIntro: 'Ha akadályokat észlel weboldalunkon, vagy tájékoztatást kér a nem akadálymentes tartalmakról, vegye fel velünk a kapcsolatot.',
				emailLabel: 'E-mail:',
				contactOutro: 'Törekszünk arra, hogy 3-5 munkanapon belül válaszoljunk megkeresésére.',
				settlementTitle: 'Végrehajtási eljárás',
				settlementDesc: 'Ha úgy véli, hogy hátrányos helyzetbe kerültek az elégtelen akadálymentesítési tervezés miatt, és kérelmét nem válaszolták meg kielégítően, felkeresheti az illetékes végrehajtó szervet.',
				alertConsent: 'Kérjük, a generálás előtt erősítse meg a felelősségkizáró nyilatkozatot.',
				lastUpdate: 'Utolsó frissítés:'
			},
			ro: {
				title: 'Declarație de accesibilitate pentru ' + companyName,
				intro: 'La <strong>' + companyName + '</strong>, ne angajăm să facem site-ul nostru web și conținutul digital cât mai accesibil posibil. Obiectivul nostru este de a oferi tuturor acces egal și nerestricționat la informațiile și serviciile furnizate, indiferent de capacitățile individuale sau de tehnologiile utilizate.',
				appliesTo: 'Această declarație de accesibilitate se aplică pentru:',
				statusTitle: 'Starea de conformitate',
				statusIntro: 'Ne aliniem la cerințele:',
				wcag: 'Ghidurilor de accesibilitate a conținutului web (WCAG) 2.1, Nivel de conformitate AA',
				bitv: 'Legii europene privind accesibilitatea (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Site-ul web este în prezent <strong>parțial</strong> în conformitate cu cerințele menționate. Lucrăm continuu pentru a identifica și reduce progresiv barierele existente.',
				measuresTitle: 'Măsuri de accesibilitate luate',
				measuresIntro: 'Pentru a îmbunătăți accesibilitatea digitală, acest site web utilizează diverse funcții de suport tehnic și vizual. Acestea includ:',
				measures: ['Dimensiuni de font ajustabile','Ajustări de contrast și culoare','Navigare cu tastatura','Evidențierea linkurilor și titlurilor','Indicatori de focus pentru utilizarea tastaturii','Funcții de citire folosind sinteza vocală a browserului','Suport pentru cititoare de ecran','Prezentare responsivă și scalabilă','Bara de instrumente de accesibilitate cu controale avansate'],
				shortcutIntro: 'Bara de instrumente de accesibilitate poate fi deschisă prin interfața site-ului web. În plus, este disponibilă o comandă rapidă de tastatură pentru acces rapid:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Note tehnice',
				techDesc: 'Funcția de citire a acestui site web utilizează tehnologia nativă SpeechSynthesis a browserului respectiv. Disponibilitatea vocilor individuale și a opțiunilor de limbă depinde de dispozitivul, sistemul de operare și browserul utilizat.',
				limitationsTitle: 'Limitări cunoscute',
				limitationsIntro: 'În ciuda eforturilor noastre, anumite conținuturi sau funcții pot fi temporar inaccesibile în totalitate. Aceasta se aplică în special pentru:',
				limitations: ['Documente PDF mai vechi','Conținut încorporat de la furnizori externi','Widget-uri sau hărți de la terți','Secțiuni individuale de pagini mai vechi'],
				limitationsOutro: 'Lucrăm continuu la îmbunătățiri și optimizări tehnice.',
				checkingTitle: 'Verificarea accesibilității',
				checkingIntro: 'Site-ul web este verificat periodic prin:',
				checking: ['Scanări automatizate de accesibilitate','Teste manuale de utilizabilitate','Teste de navigare cu tastatura','Revizuiri tehnice ale problemelor relevante pentru WCAG'],
				contactTitle: 'Feedback și contact',
				contactIntro: 'Dacă observați bariere pe site-ul nostru web sau aveți nevoie de informații despre conținutul inaccesibil, vă rugăm să ne contactați.',
				emailLabel: 'E-mail:',
				contactOutro: 'Ne străduim să răspundem solicitării dvs. în termen de 3 până la 5 zile lucrătoare.',
				settlementTitle: 'Procedura de aplicare',
				settlementDesc: 'Dacă considerați că ați fost dezavantajat din cauza unui design de accesibilitate insuficient și solicitarea dvs. nu a primit un răspuns satisfăcător, puteți contacta organismul de aplicare responsabil.',
				alertConsent: 'Vă rugăm să confirmați declarația de excludere a răspunderii înainte de a genera.',
				lastUpdate: 'Ultima actualizare:'
			},
			sk: {
				title: 'Vyhlásenie o prístupnosti pre ' + companyName,
				intro: 'V spoločnosti <strong>' + companyName + '</strong> sme odhodlaní urobiť náš web a digitálny obsah čo najprístupnejšími. Naším cieľom je zabezpečiť rovnaký a neobmedzený prístup k poskytovaným informáciám a službám pre každého, bez ohľadu na individuálne schopnosti alebo používané technológie.',
				appliesTo: 'Toto vyhlásenie o prístupnosti sa vzťahuje na:',
				statusTitle: 'Stav súladu s požiadavkami',
				statusIntro: 'Riadime sa požiadavkami:',
				wcag: 'Usmernení pre prístupnosť webového obsahu (WCAG) 2.1, úroveň zhody AA',
				bitv: 'Európskeho zákona o prístupnosti (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Webová stránka je v súčasnosti <strong>čiastočne</strong> v súlade s uvedenými požiadavkami. Neustále pracujeme na identifikácii a postupnom odstraňovaní existujúcich bariér.',
				measuresTitle: 'Prijaté opatrenia pre prístupnosť',
				measuresIntro: 'Na zlepšenie digitálnej prístupnosti využíva táto webová stránka rôzne funkcie technickej a vizuálnej podpory. Patrí medzi ne:',
				measures: ['Nastaviteľné veľkosti písma','Úpravy kontrastu a farieb','Navigácia klávesnicou','Zvýraznenie odkazov a nadpisov','Indikátory zamerania pre ovládanie klávesnicou','Funkcie čítania pomocou prevodu textu na reč v prehliadači','Podpora pre čítačky obrazovky','Responzívna a škálovateľná prezentácia','Panel nástrojov prístupnosti s pokročilými ovládacími prvkami'],
				shortcutIntro: 'Panel nástrojov prístupnosti je možné otvoriť prostredníctvom rozhrania webovej stránky. K dispozícii je aj klávesová skratka pre rýchly prístup:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Technické poznámky',
				techDesc: 'Funkcia čítania tejto webovej stránky využíva natívnu technológiu SpeechSynthesis príslušného prehliadača. Dostupnosť jednotlivých hlasov a jazykových možností závisí od použitého zariadenia, operačného systému a prehliadača.',
				limitationsTitle: 'Známe obmedzenia',
				limitationsIntro: 'Napriek našej snahe nemusia byť určité obsah alebo funkcie dočasne plne prístupné. Týka sa to najmä:',
				limitations: ['Starších dokumentov PDF','Vloženého obsahu od externých poskytovateľov','Widgetov alebo máp tretích strán','Jednotlivých starších sekcií stránok'],
				limitationsOutro: 'Neustále pracujeme na vylepšeniach a technických optimalizáciách.',
				checkingTitle: 'Overenie prístupnosti',
				checkingIntro: 'Webová stránka sa pravidelne overuje prostredníctvom:',
				checking: ['Automatizovaných skenov prístupnosti','Manuálnych testov použiteľnosti','Testov navigácie klávesnicou','Technických preskúmaní problémov relevantných pre WCAG'],
				contactTitle: 'Spätná väzba a kontakt',
				contactIntro: 'Ak si na našej webovej stránke všimnete bariéry alebo potrebujete informácie o neprístupnom obsahu, kontaktujte nás.',
				emailLabel: 'E-mail:',
				contactOutro: 'Snažíme sa odpovedať na vašu žiadosť do 3 až 5 pracovných dní.',
				settlementTitle: 'Postup presadzovania',
				settlementDesc: 'Ak sa domnievate, že ste boli znevýhodnení z dôvodu nedostatočného bezbariérového dizajnu a vaša žiadosť nebola uspokojivo zodpovedaná, môžete sa obrátiť na príslušný orgán presadzovania.',
				alertConsent: 'Pred vygenerovaním potvrďte vyhlásenie o vylúčení zodpovednosti.',
				lastUpdate: 'Posledná aktualizácia:'
			},
			sl: {
				title: 'Izjava o dostopnosti za ' + companyName,
				intro: 'Pri podjetju <strong>' + companyName + '</strong> si prizadevamo, da bi naše spletno mesto in digitalno vsebino naredili kar se da dostopne. Naš cilj je vsem zagotoviti enak in neomejen dostop do zagotovljenih informacij in storitev, ne glede na individualne sposobnosti ali uporabljene tehnologije.',
				appliesTo: 'Ta izjava o dostopnosti velja za:',
				statusTitle: 'Stanje skladnosti',
				statusIntro: 'Usklajujemo se z zahtevami:',
				wcag: 'Smernic za dostopnost spletnih vsebin (WCAG) 2.1, raven skladnosti AA',
				bitv: 'Evropskega zakona o dostopnosti (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Spletno mesto je trenutno <strong>delno</strong> skladno z omenjenimi zahtevami. Nenehno delamo na prepoznavanju in postopnem zmanjševanju obstoječih ovir.',
				measuresTitle: 'Sprejeti ukrepi dostopnosti',
				measuresIntro: 'Za izboljšanje digitalne dostopnosti to spletno mesto uporablja različne funkcije tehnične in vizualne podpore. Te vključujejo:',
				measures: ['Nastavljive velikosti pisave','Prilagoditve kontrasta in barve','Navigacija s tipkovnico','Poudarjanje povezav in naslovov','Indikatorji fokusa za uporabo tipkovnice','Funkcije branja z bralnikom besedila v brskalniku','Podpora za bralce zaslona','Odzivna in skalabilna predstavitev','Orodna vrstica dostopnosti z naprednimi kontrolami'],
				shortcutIntro: 'Orodno vrstico dostopnosti lahko odprete prek vmesnika spletnega mesta. Poleg tega je na voljo bližnjica na tipkovnici za hiter dostop:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Tehnične opombe',
				techDesc: 'Funkcija branja tega spletnega mesta uporablja nativno tehnologijo SpeechSynthesis ustreznega brskalnika. Razpoložljivost posameznih glasov in jezikovnih možnosti je odvisna od naprave, operacijskega sistema in brskalnika, ki jih uporabljate.',
				limitationsTitle: 'Znane omejitve',
				limitationsIntro: 'Kljub našim prizadevanjem posamezne vsebine ali funkcije morda začasno niso v celoti dostopne. To velja zlasti za:',
				limitations: ['Starejše dokumente PDF','Vdelano vsebino zunanjih ponudnikov','Pripomočke ali zemljevide tretjih oseb','Posamezne starejše razdelke strani'],
				limitationsOutro: 'Nenehno delamo na izboljšavah in tehničnih optimizacijah.',
				checkingTitle: 'Preverjanje dostopnosti',
				checkingIntro: 'Spletno mesto se redno preverja z:',
				checking: ['Avtomatiziranimi pregledi dostopnosti','Ročnimi testi uporabnosti','Testi navigacije s tipkovnico','Tehničnimi pregledi težav, pomembnih za WCAG'],
				contactTitle: 'Povratne informacije in stik',
				contactIntro: 'Če opazite ovire na našem spletnem mestu ali potrebujete informacije o nedostopni vsebini, nas prosimo kontaktirajte.',
				emailLabel: 'E-pošta:',
				contactOutro: 'Prizadevamo si, da na vašo zahtevo odgovorimo v roku 3 do 5 delovnih dni.',
				settlementTitle: 'Postopek izvrševanja',
				settlementDesc: 'Če menite, da ste bili oškodovani zaradi nezadostnega dostopnega oblikovanja in vaša zahteva ni bila zadovoljivo obravnavana, se lahko obrnete na pristojni organ za izvrševanje.',
				alertConsent: 'Pred ustvarjanjem potrdite izjavo o omejitvi odgovornosti.',
				lastUpdate: 'Zadnja posodobitev:'
			},
			hr: {
				title: 'Izjava o pristupačnosti za ' + companyName,
				intro: 'U tvrtki <strong>' + companyName + '</strong> predani smo tome da naše web mjesto i digitalne sadržaje učinimo što pristupačnijima. Naš je cilj svima osigurati jednak i neograničen pristup dostupnim informacijama i uslugama, bez obzira na individualne sposobnosti ili korištene tehnologije.',
				appliesTo: 'Ova izjava o pristupačnosti odnosi se na:',
				statusTitle: 'Status usklađenosti',
				statusIntro: 'Usklađujemo se s uvjetima:',
				wcag: 'Smjernica za pristupačnost web sadržaja (WCAG) 2.1, razina usklađenosti AA',
				bitv: 'Europskog zakona o pristupačnosti (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Web mjesto je trenutno <strong>djelomično</strong> usklađeno s navedenim uvjetima. Kontinuirano radimo na prepoznavanju i postupnom smanjivanju postojećih prepreka.',
				measuresTitle: 'Poduzete mjere pristupačnosti',
				measuresIntro: 'Radi poboljšanja digitalne pristupačnosti, ovo web mjesto koristi razne tehničke i vizualne funkcije podrške. One uključuju:',
				measures: ['Podesive veličine fonta','Prilagodbe kontrasta i boje','Navigacija tipkovnicom','Isticanje veza i naslova','Indikatori fokusa za upotrebu tipkovnice','Funkcije čitanja pomoću pretvorbe teksta u govor u pregledniku','Podrška za čitače zaslona','Responzivna i skalabilna prezentacija','Traka alata za pristupačnost s naprednim kontrolama'],
				shortcutIntro: 'Traka alata za pristupačnost može se otvoriti putem sučelja web mjesta. Osim toga, dostupna je tipkovnička prečica za brzi pristup:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Tehničke napomene',
				techDesc: 'Funkcija čitanja ovog web mjesta koristi nativnu SpeechSynthesis tehnologiju odgovarajućeg preglednika. Dostupnost pojedinačnih glasova i jezičnih opcija ovisi o korištenom uređaju, operativnom sustavu i pregledniku.',
				limitationsTitle: 'Poznata ograničenja',
				limitationsIntro: 'Unatoč našim naporima, pojedini sadržaji ili funkcije privremeno možda nisu u potpunosti pristupačni. To se posebno odnosi na:',
				limitations: ['Starije PDF dokumente','Ugrađeni sadržaj vanjskih pružatelja','Widgete ili karte trećih strana','Pojedine starije odjeljke stranice'],
				limitationsOutro: 'Kontinuirano radimo na poboljšanjima i tehničkim optimizacijama.',
				checkingTitle: 'Provjera pristupačnosti',
				checkingIntro: 'Web mjesto se redovito provjerava putem:',
				checking: ['Automatiziranih provjera pristupačnosti','Ručnih testova upotrebljivosti','Testova navigacije tipkovnicom','Tehničkih pregleda problema relevantnih za WCAG'],
				contactTitle: 'Povratne informacije i kontakt',
				contactIntro: 'Ako primijetite prepreke na našem web mjestu ili trebate informacije o nepristupačnom sadržaju, molimo kontaktirajte nas.',
				emailLabel: 'E-pošta:',
				contactOutro: 'Nastojimo odgovoriti na vaš zahtjev u roku od 3 do 5 radnih dana.',
				settlementTitle: 'Postupak provedbe',
				settlementDesc: 'Ako smatrate da ste oštećeni zbog nedovoljnog dizajna pristupačnosti i vaš zahtjev nije zadovoljavajuće odgovoren, možete se obratiti nadležnom tijelu za provedbu.',
				alertConsent: 'Molimo potvrdite izjavu o odricanju od odgovornosti prije generiranja.',
				lastUpdate: 'Posljednje ažuriranje:'
			},
			el: {
				title: 'Δήλωση Προσβασιμότητας για ' + companyName,
				intro: 'Στην <strong>' + companyName + '</strong>, δεσμευόμαστε να κάνουμε τον ιστότοπό μας και το ψηφιακό μας περιεχόμενο όσο το δυνατόν πιο προσβάσιμα. Στόχος μας είναι να παρέχουμε σε όλους ίση και απεριόριστη πρόσβαση στις πληροφορίες και τις υπηρεσίες που παρέχουμε, ανεξάρτητα από ατομικές ικανότητες ή χρησιμοποιούμενες τεχνολογίες.',
				appliesTo: 'Αυτή η δήλωση προσβασιμότητας εφαρμόζεται σε:',
				statusTitle: 'Κατάσταση Συμμόρφωσης',
				statusIntro: 'Ευθυγραμμιζόμαστε με τις απαιτήσεις:',
				wcag: 'Οδηγιών Προσβασιμότητας Περιεχομένου Ιστού (WCAG) 2.1, Επίπεδο Συμμόρφωσης AA',
				bitv: 'Ευρωπαϊκής Πράξης Προσβασιμότητας (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Ο ιστότοπος συμμορφώνεται επί του παρόντος <strong>μερικώς</strong> με τις αναφερόμενες απαιτήσεις. Εργαζόμαστε συνεχώς για την αναγνώριση και τη σταδιακή μείωση των υπαρχόντων φραγμών.',
				measuresTitle: 'Μέτρα Προσβασιμότητας που Λήφθηκαν',
				measuresIntro: 'Για τη βελτίωση της ψηφιακής προσβασιμότητας, αυτός ο ιστότοπος χρησιμοποιεί διάφορες τεχνικές και οπτικές λειτουργίες υποστήριξης. Αυτές περιλαμβάνουν:',
				measures: ['Ρυθμιζόμενα μεγέθη γραμματοσειράς','Ρυθμίσεις αντίθεσης και χρώματος','Πλοήγηση με πληκτρολόγιο','Επισήμανση συνδέσμων και επικεφαλίδων','Δείκτες εστίασης για χρήση πληκτρολογίου','Λειτουργίες ανάγνωσης με σύνθεση ομιλίας του προγράμματος περιήγησης','Υποστήριξη για αναγνώστες οθόνης','Αποκριτική και επεκτάσιμη παρουσίαση','Γραμμή εργαλείων προσβασιμότητας με προηγμένα στοιχεία ελέγχου'],
				shortcutIntro: 'Η γραμμή εργαλείων προσβασιμότητας μπορεί να ανοιχτεί μέσω της διεπαφής του ιστότοπου. Επιπλέον, διατίθεται συντόμευση πληκτρολογίου για γρήγορη πρόσβαση:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Τεχνικές Σημειώσεις',
				techDesc: 'Η λειτουργία ανάγνωσης αυτού του ιστότοπου χρησιμοποιεί την εγγενή τεχνολογία SpeechSynthesis του αντίστοιχου προγράμματος περιήγησης. Η διαθεσιμότητα μεμονωμένων φωνών και επιλογών γλώσσας εξαρτάται από τη συσκευή, το λειτουργικό σύστημα και το πρόγραμμα περιήγησης που χρησιμοποιείται.',
				limitationsTitle: 'Γνωστοί Περιορισμοί',
				limitationsIntro: 'Παρά τις προσπάθειές μας, ορισμένα περιεχόμενα ή λειτουργίες ενδέχεται προσωρινά να μην είναι πλήρως προσβάσιμα. Αυτό ισχύει ιδιαίτερα για:',
				limitations: ['Παλαιότερα έγγραφα PDF','Ενσωματωμένο περιεχόμενο από εξωτερικούς παρόχους','Widget ή χάρτες τρίτων','Μεμονωμένες παλαιότερες ενότητες σελίδας'],
				limitationsOutro: 'Εργαζόμαστε συνεχώς σε βελτιώσεις και τεχνικές βελτιστοποιήσεις.',
				checkingTitle: 'Επαλήθευση Προσβασιμότητας',
				checkingIntro: 'Ο ιστότοπος επαληθεύεται τακτικά μέσω:',
				checking: ['Αυτοματοποιημένων σαρώσεων προσβασιμότητας','Χειροκίνητων δοκιμών χρηστικότητας','Δοκιμών πλοήγησης με πληκτρολόγιο','Τεχνικών αξιολογήσεων ζητημάτων σχετικών με το WCAG'],
				contactTitle: 'Σχόλια και Επικοινωνία',
				contactIntro: 'Εάν παρατηρήσετε φραγμούς στον ιστότοπό μας ή χρειάζεστε πληροφορίες για μη προσβάσιμο περιεχόμενο, παρακαλούμε επικοινωνήστε μαζί μας.',
				emailLabel: 'Email:',
				contactOutro: 'Προσπαθούμε να απαντάμε στο αίτημά σας εντός 3 έως 5 εργάσιμων ημερών.',
				settlementTitle: 'Διαδικασία Επιβολής',
				settlementDesc: 'Εάν πιστεύετε ότι έχετε μειονεκτήσει λόγω ανεπαρκούς σχεδιασμού προσβασιμότητας και το αίτημά σας δεν έχει απαντηθεί ικανοποιητικά, μπορείτε να επικοινωνήσετε με τον αρμόδιο φορέα επιβολής.',
				alertConsent: 'Παρακαλώ επιβεβαιώστε την αποποίηση ευθύνης πριν από τη δημιουργία.',
				lastUpdate: 'Τελευταία ενημέρωση:'
			},
			bg: {
				title: 'Декларация за достъпност за ' + companyName,
				intro: 'В <strong>' + companyName + '</strong> се ангажираме да направим нашия уебсайт и дигиталното ни съдържание възможно най-достъпни. Нашата цел е да осигурим равен и неограничен достъп до предоставяната информация и услуги за всички, независимо от индивидуалните способности или използваните технологии.',
				appliesTo: 'Настоящата декларация за достъпност се прилага за:',
				statusTitle: 'Статус на съответствие',
				statusIntro: 'Ние се придържаме към изискванията на:',
				wcag: 'Указания за достъпност на уеб съдържание (WCAG) 2.1, ниво на съответствие AA',
				bitv: 'Европейски акт за достъпност (EAA)',
				en301: 'EN 301 549',
				statusPartly: 'Уебсайтът в момента е <strong>частично</strong> в съответствие с посочените изисквания. Непрекъснато работим за идентифициране и постепенно намаляване на съществуващите бариери.',
				measuresTitle: 'Предприети мерки за достъпност',
				measuresIntro: 'За подобряване на дигиталната достъпност, този уебсайт използва различни функции за техническа и визуална поддръжка. Те включват:',
				measures: ['Регулируеми размери на шрифта','Корекции на контраст и цвят','Навигация с клавиатура','Маркиране на връзки и заглавия','Индикатори на фокус за употреба с клавиатура','Функции за четене с помощта на синтез на реч в браузъра','Поддръжка за четци на екрана','Адаптивна и мащабируема презентация','Лента с инструменти за достъпност с разширени контроли'],
				shortcutIntro: 'Лентата с инструменти за достъпност може да бъде отворена чрез интерфейса на уебсайта. Освен това е налична клавишна комбинация за бърз достъп:',
				winShortcut: 'Windows: <kbd>Alt</kbd> + <kbd>.</kbd>',
				macShortcut: 'macOS: <kbd>⌘</kbd> + <kbd>.</kbd>',
				techTitle: 'Технически бележки',
				techDesc: 'Функцията за четене на този уебсайт използва нативната технология SpeechSynthesis на съответния браузър. Наличността на отделни гласове и езикови опции зависи от използваното устройство, операционна система и браузър.',
				limitationsTitle: 'Известни ограничения',
				limitationsIntro: 'Въпреки нашите усилия, отделни съдържания или функции може временно да не са напълно достъпни. Това се отнася по-специално за:',
				limitations: ['По-стари PDF документи','Вградено съдържание от външни доставчици','Уиджети или карти на трети страни','Отделни по-стари раздели на страниците'],
				limitationsOutro: 'Непрекъснато работим по подобрения и технически оптимизации.',
				checkingTitle: 'Проверка на достъпността',
				checkingIntro: 'Уебсайтът се проверява редовно чрез:',
				checking: ['Автоматизирани сканирания на достъпност','Ръчни тестове за използваемост','Тестове за навигация с клавиатура','Технически прегледи на проблеми, свързани с WCAG'],
				contactTitle: 'Обратна връзка и контакт',
				contactIntro: 'Ако забележите бариери на нашия уебсайт или имате нужда от информация за недостъпно съдържание, моля, свържете се с нас.',
				emailLabel: 'Имейл:',
				contactOutro: 'Стремим се да отговорим на вашата заявка в рамките на 3 до 5 работни дни.',
				settlementTitle: 'Процедура за прилагане',
				settlementDesc: 'Ако смятате, че сте поставени в неравностойно положение поради недостатъчен дизайн на достъпност и заявката ви не е получила задоволителен отговор, можете да се свържете с компетентния орган за прилагане.',
				alertConsent: 'Моля, потвърдете декларацията за отказ от отговорност преди генерирането.',
				lastUpdate: 'Последна актуализация:'
			}
		}; }

		// Helper to update sections hidden input
		function updateSectionsData() {
			var sections = [];
			$('#me-sections-sortable-list .me-section-item-new').each(function() {
				var $item = $(this);
				sections.push({
					id: $item.data('section-id'),
					name: $item.find('.me-section-name-new').text().trim(),
					enabled: $item.find('.me-section-toggle-new').prop('checked'),
					content: $item.attr('data-section-content') || ''
				});
			});
			$('#me-statement-sections-input').val(JSON.stringify(sections));
		}

		// Live word count and reading time
		function updateWordCount() {
			var text = '';
			if (typeof tinymce !== 'undefined' && tinymce.get('me_statement_editor')) {
				text = tinymce.get('me_statement_editor').getContent({format: 'text'});
			} else {
				text = $('#me_statement_editor').val() || '';
			}
			var words = text.trim() ? text.trim().split(/\s+/).length : 0;
			var readTime = Math.ceil(words / 150);
			$('#me-editor-word-count').text(words);
			$('#me-editor-reading-time').text(readTime);
		}

		// Init TinyMCE events for live statistics
		if (typeof tinymce !== 'undefined') {
			setTimeout(function() {
				var ed = tinymce.get('me_statement_editor');
				if (ed) {
					ed.on('keyup change setcontent', function() {
						updateWordCount();
					});
					updateWordCount();
				}
			}, 1500);
		}
		$('#me_statement_editor').on('keyup input change', function() {
			updateWordCount();
		});

		// Sortable setup
		if ($('#me-sections-sortable-list').length) {
			$('#me-sections-sortable-list').sortable({
				handle: '.me-section-grab-handle-new',
				placeholder: 'me-section-item-placeholder',
				stop: function() {
					updateSectionsData();
				}
			});
		}

		// Toggle handler
		$(document).on('change', '.me-section-toggle-new', function() {
			updateSectionsData();
		});

		// Workspace tab switcher
		$(document).on('click', '.me-workspace-tab-btn-new', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var tab = $btn.data('workspace-tab');
			$('.me-workspace-tab-btn-new').removeClass('active');
			$btn.addClass('active');
			if (tab === 'editor') {
				$('#me-workspace-view-editor').addClass('active').show();
				$('#me-workspace-view-template').removeClass('active').hide();
			} else {
				$('#me-workspace-view-editor').removeClass('active').hide();
				$('#me-workspace-view-template').addClass('active').show();
			}
		});

		// Template card selection
		$(document).on('click', '.me-template-card-new', function() {
			$('.me-template-card-new').removeClass('active');
			$(this).addClass('active');
			var style = $(this).data('template-style');
			alert('Template style "' + style + '" selected. Click "Regenerate" under Quick Actions to apply.');
			$('.me-workspace-tab-btn-new[data-workspace-tab="editor"]').trigger('click');
		});

		// Add custom section
		$(document).on('click', '#me-add-section-btn', function(e) {
			e.preventDefault();
			var title = prompt(meAccessibilityAdmin.translations.addSectionTitle || 'Abschnitts-Titel eingeben:');
			if (!title) return;
			var content = prompt(meAccessibilityAdmin.translations.addSectionContent || 'Abschnitts-Inhalt eingeben (HTML erlaubt):');
			if (!content) content = '';

			var id = 'custom_' + Date.now();
			var newItem = '<li class="me-section-item-new" data-section-id="' + id + '" data-section-content="' + content.replace(/"/g, '&quot;') + '">' +
				'<span class="me-section-grab-handle-new dashicons dashicons-menu"></span>' +
				'<span class="me-section-name-new">' + title + '</span>' +
				'<label class="me-switch-small-new">' +
					'<input type="checkbox" class="me-section-toggle-new" checked />' +
					'<span class="me-slider-small-new round"></span>' +
				'</label>' +
				'<span class="me-section-actions-new dashicons dashicons-ellipsis"></span>' +
			'</li>';

			$('#me-sections-sortable-list').append(newItem);
			updateSectionsData();
		});

		// Sync indicator
		$(document).on('click', '#me-sync-indicator-btn', function() {
			var $icon = $(this);
			$icon.addClass('dashicons-spin');
			setTimeout(function() {
				$icon.removeClass('dashicons-spin');
				var d = new Date();
				var timeStr = ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
				$('#me-last-saved-time').text(timeStr);
				updateWordCount();
			}, 800);
		});

		// REGENERATE Action
		$(document).on('click', '#me-action-regenerate', function(e) {
			e.preventDefault();
			
			var companyName   = $('#me-statement-company-name').val().trim() || 'Example';
			var emailAddress  = $('#me-statement-email').val().trim() || 'info@domain.de';
			var websiteUrl    = $('#me-statement-url').val().trim() || 'www.domain.de';
			var statementLang = $('#me-statement-lang').val() || 'en';
			var templatesList = getTemplates(companyName);
			var t = templatesList[statementLang] || templatesList.en;
			
			var dateOptions    = { year: 'numeric', month: 'long', day: 'numeric' };
			var locales        = {
				bg: 'bg-BG', cs: 'cs-CZ', da: 'da-DK', de: 'de-DE',
				el: 'el-GR', en: 'en-US', es: 'es-ES', fi: 'fi-FI',
				fr: 'fr-FR', hr: 'hr-HR', hu: 'hu-HU', it: 'it-IT',
				nl: 'nl-NL', pl: 'pl-PL', pt: 'pt-PT', ro: 'ro-RO',
				sk: 'sk-SK', sl: 'sl-SI', sv: 'sv-SE', vi: 'vi-VN'
			};
			var activeLocale   = locales[statementLang] || 'en-US';
			var currentDateStr = new Date().toLocaleDateString(activeLocale, dateOptions);

			var htmlBlocks = {
				introduction: '<h1>' + t.title + '</h1>' +
							  '<p>' + t.intro + '</p>' +
							  '<p>' + t.appliesTo + '<br>' +
							  '<a href="https://' + websiteUrl + '" target="_blank" rel="noopener">' + websiteUrl + '</a></p>',
				conformance_status: '<h2>' + t.statusTitle + '</h2>' +
									'<p>' + t.statusIntro + '</p>' +
									'<ul>' +
									'<li>' + t.wcag + '</li>' +
									'<li>' + t.bitv + '</li>' +
									'<li>' + t.en301 + '</li>' +
									'</ul>',
				feedback: '<h2>' + (t.feedbackTitle || t.contactTitle) + '</h2>' +
						  '<p>' + t.contactIntro + '</p>' +
						  '<p>' + t.shortcutIntro + '</p>' +
						  '<ul>' +
						  '<li>' + t.winShortcut + '</li>' +
						  '<li>' + t.macShortcut + '</li>' +
						  '</ul>',
				compatibility: '<h2>' + (t.compatibilityTitle || 'Kompatibilität') + '</h2>' +
							   '<p>' + t.statusPartly + '</p>',
				technical_information: '<h2>' + t.techTitle + '</h2>' +
									   '<p>' + t.techDesc + '</p>' +
									   '<p>' + t.measuresIntro + '</p>' +
									   '<ul>' +
									   t.measures.map(function(item) { return '<li>' + item + '</li>'; }).join('') +
									   '</ul>',
				limitations: '<h2>' + t.limitationsTitle + '</h2>' +
							 '<p>' + t.limitationsIntro + '</p>' +
							 '<ul>' +
							 t.limitations.map(function(item) { return '<li>' + item + '</li>'; }).join('') +
							 '</ul>' +
							 '<p>' + t.limitationsOutro + '</p>',
				assessment_approach: '<h2>' + t.checkingTitle + '</h2>' +
									 '<p>' + t.checkingIntro + '</p>' +
									 '<ul>' +
									 t.checking.map(function(item) { return '<li>' + item + '</li>'; }).join('') +
									 '</ul>',
				contact_information: '<h2>' + (t.contactInfoTitle || 'Kontaktangaben') + '</h2>' +
									 '<p>' + t.emailLabel + ' <a href="mailto:' + emailAddress + '">' + emailAddress + '</a></p>' +
									 '<p>' + t.contactOutro + '</p>' +
									 '<p><strong>' + t.lastUpdate + '</strong><br>' + currentDateStr + '</p>'
			};

			var finalHtml = '';
			var $list = $('#me-sections-sortable-list .me-section-item-new');
			if ($list.length > 0) {
				$list.each(function() {
					var $item = $(this);
					var id = $item.data('section-id');
					var isEnabled = $item.find('.me-section-toggle-new').prop('checked');
					if (isEnabled) {
						if (htmlBlocks[id]) {
							finalHtml += htmlBlocks[id];
						} else {
							// Custom section support
							var customContent = $item.attr('data-section-content') || '';
							finalHtml += '<h2>' + $item.find('.me-section-name-new').text().trim() + '</h2>' + customContent;
						}
					}
				});
			} else {
				var sectionOrder = [
					'introduction',
					'conformance_status',
					'feedback',
					'compatibility',
					'technical_information',
					'limitations',
					'assessment_approach',
					'contact_information'
				];
				sectionOrder.forEach(function(id) {
					if (htmlBlocks[id]) {
						finalHtml += htmlBlocks[id];
					}
				});
			}

			if (typeof tinymce !== 'undefined' && tinymce.get('me_statement_editor')) {
				tinymce.get('me_statement_editor').setContent(finalHtml);
			} else {
				$('#me_statement_editor').val(finalHtml);
			}
			updateWordCount();
			
			// Increment version and set date
			var currentVer = parseFloat($('#me-statement-version-input').val()) || 1.0;
			var newVer = (currentVer + 0.1).toFixed(1);
			$('#me-statement-version-input').val(newVer);
			$('#me-info-val-version').text(newVer);

			var d = new Date();
			var dateStr = d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
			$('#me-statement-last-updated-input').val(dateStr);
			$('#me-info-val-updated').text(dateStr);

			var nextReview = new Date(d.getFullYear() + 1, d.getMonth(), d.getDate()).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
			$('#me-info-val-next').text(nextReview);
		});

		// PDF EXPORT Action
		$(document).on('click', '#me-action-pdf', function(e) {
			e.preventDefault();
			var content = '';
			if (typeof tinymce !== 'undefined' && tinymce.get('me_statement_editor')) {
				content = tinymce.get('me_statement_editor').getContent();
			} else {
				content = $('#me_statement_editor').val() || '';
			}
			var printWindow = window.open('', '_blank', 'width=800,height=600');
			printWindow.document.write('<html><head><title>Accessibility Statement</title>');
			printWindow.document.write('<style>');
			printWindow.document.write('body { font-family: sans-serif; padding: 40px; line-height: 1.6; color: #1e293b; }');
			printWindow.document.write('h1 { font-size: 28px; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 24px; }');
			printWindow.document.write('h2 { font-size: 20px; margin-top: 32px; margin-bottom: 16px; }');
			printWindow.document.write('kbd { background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; padding: 2px 6px; font-size: 11px; }');
			printWindow.document.write('a { color: #2563eb; text-decoration: none; }');
			printWindow.document.write('</style></head><body>');
			printWindow.document.write('<div class="me-statement-content">' + content + '</div>');
			printWindow.document.write('</body></html>');
			printWindow.document.close();
			printWindow.focus();
			setTimeout(function() {
				printWindow.print();
				printWindow.close();
			}, 500);
		});

		var $historyModal = $('#me-statement-history-modal');

		$(document).on('click', '#me-view-history-btn', function(e) {
			e.preventDefault();
			
			$historyModal.css('display', 'flex').hide().fadeIn(200);
			$('body').addClass('me-modal-open');
			
			$('#me-statement-history-loading').show();
			$('#me-statement-history-tbody').empty();
			
			$.ajax({
				url: meAccessibilityAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'me_get_statement_history',
					nonce: meAccessibilityAdmin.scannerNonce
				},
				success: function(response) {
					$('#me-statement-history-loading').hide();
					if (response.success && response.data.history && response.data.history.length > 0) {
						var historyList = response.data.history;
						renderHistoryTable(historyList);
					} else {
						$('#me-statement-history-tbody').html('<tr><td colspan="3" style="text-align:center; padding: 20px; color:#64748b;">' + meAccessibilityAdmin.translations.noHistoryFound + '</td></tr>');
					}
				},
				error: function() {
					$('#me-statement-history-loading').hide();
					$('#me-statement-history-tbody').html('<tr><td colspan="3" style="text-align:center; padding: 20px; color:#ef4444;">Error loading history.</td></tr>');
				}
			});
		});

		function renderHistoryTable(list) {
			var $tbody = $('#me-statement-history-tbody');
			$tbody.empty();
			
			list.forEach(function(item, idx) {
				var versionStr = $('<div/>').text(item.version).html();
				var dateStr    = $('<div/>').text(item.last_updated).html();
				
				var restoreText = meAccessibilityAdmin.translations.restore || 'Wiederherstellen';
				
				var rowHtml = '<tr>';
				rowHtml += '<td><strong>v' + versionStr + '</strong></td>';
				rowHtml += '<td>' + dateStr + '</td>';
				rowHtml += '<td style="text-align:right;">';
				rowHtml += '  <button type="button" class="button button-small me-restore-version-btn" data-index="' + idx + '"><span class="dashicons dashicons-undo" style="font-size:14px; width:14px; height:14px; margin-top:3px;"></span> ' + restoreText + '</button>';
				rowHtml += '</td>';
				rowHtml += '</tr>';
				
				var $row = $(rowHtml);
				$row.find('.me-restore-version-btn').data('content', item.text).data('version', item.version);
				$tbody.append($row);
			});
		}

		// Click handler for Restore button
		$(document).on('click', '.me-restore-version-btn', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var content = $btn.data('content');
			var version = $btn.data('version');
			
			if (confirm(meAccessibilityAdmin.translations.restoreConfirm)) {
				// Restore to editor
				if (typeof tinymce !== 'undefined' && tinymce.get('me_statement_editor')) {
					tinymce.get('me_statement_editor').setContent(content);
				} else {
					$('#me_statement_editor').val(content);
				}
				updateWordCount();
				
				// Update version indicator text
				$('#me-statement-version-input').val(version);
				$('#me-info-val-version').text(version);
				
				// Close modal
				$historyModal.fadeOut(200);
				$('body').removeClass('me-modal-open');
				
				// Alert success
				var successMsg = meAccessibilityAdmin.translations.restoreSuccess.replace('%s', version);
				alert(successMsg);
			}
		});

		// Close modal handlers
		$(document).on('click', '#me-statement-history-modal .me-admin-modal-close, #me-statement-history-modal .me-admin-modal-close-btn', function(e) {
			e.preventDefault();
			$historyModal.fadeOut(200);
			$('body').removeClass('me-modal-open');
		});

		$historyModal.on('click', function(e) {
			if ($(e.target).is($historyModal)) {
				$historyModal.fadeOut(200);
				$('body').removeClass('me-modal-open');
			}
		});

		// Auto-save: ON simulation
		setInterval(function() {
			var d = new Date();
			var timeStr = ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2);
			$('#me-last-saved-time').text(timeStr);
		}, 60000);


	// ==========================================
	// 4. WORDPRESS MEDIATHEK-PICKER (Admin-Logo)
	// ==========================================

	/**
	 * wp.media Frame – wird einmalig erstellt und wiederverwendet,
	 * um doppeltes Laden zu verhindern.
	 */
	var meMediaFrame = null;

	/**
	 * Öffnet den WordPress Mediathek-Dialog.
	 * data-target  → ID des URL-Eingabefeldes
	 * data-preview → ID des Vorschau-Containers
	 */
	$(document).on('click', '.me-media-picker-btn', function(e) {
		e.preventDefault();

		var $btn       = $(this);
		var targetId   = $btn.data('target');
		var previewId  = $btn.data('preview');

		// Alten Frame verwerfen und neu erstellen (vermeidet Callback-Überschneidungen)
		if (meMediaFrame) {
			meMediaFrame.detach();
		}

		meMediaFrame = wp.media({
			title:    meAccessibilityAdmin.mediaTitle  || 'Bild auswählen',
			button:   { text: meAccessibilityAdmin.mediaButton || 'Dieses Bild verwenden' },
			library:  { type: 'image' },
			multiple: false
		});

		// Wenn der Benutzer ein Bild auswählt und auf "Dieses Bild verwenden" klickt
		meMediaFrame.on('select', function() {
			var attachment = meMediaFrame.state().get('selection').first().toJSON();
			var url        = attachment.url;

			// URL in das Eingabefeld schreiben
			$('#' + targetId).val(url);

			// Bildvorschau aktualisieren
			var $preview = $('#' + previewId);
			$preview.find('img').attr('src', url);
			$preview.removeClass('hidden');

			// "Entfernen"-Button einblenden
			$('.me-media-remove-btn[data-target="' + targetId + '"]').removeClass('hidden');
		});
meMediaFrame.open();
	});

	/**
	 * Bild entfernen: URL leeren, Vorschau ausblenden, Entfernen-Button verstecken.
	 */
	$(document).on('click', '.me-media-remove-btn', function(e) {
		e.preventDefault();

		var $btn      = $(this);
		var targetId  = $btn.data('target');
		var previewId = $btn.data('preview');

		// Eingabefeld leeren
		$('#' + targetId).val('');

		// Vorschau ausblenden
		$('#' + previewId).addClass('hidden').find('img').attr('src', '');

		// Entfernen-Button selbst ausblenden
		$btn.addClass('hidden');
	});

	var $detailsModal = $('#me-scan-details-modal');
	var allIssues = [];
	var currentFilter = 'all';

	$(document).on('click', '.me-view-details-btn', function(e) {
		e.preventDefault();
		var scanId = $(this).data('scan-id');
		if (!scanId) return;

		// Modal öffnen
		$detailsModal.css('display', 'flex').hide().fadeIn(200);
		$('body').addClass('me-modal-open');

		// Loading-State aktivieren
		$('#me-scan-issues-loading').show();
		$('#me-scan-issues-body').empty();
		$('#me-critical-count').text('0');
		$('#me-warning-count').text('0');
		$('#me-notice-count').text('0');
		$('.me-filter-btn').removeClass('active');
		$('.me-filter-btn[data-filter="all"]').addClass('active');
		currentFilter = 'all';

		// AJAX-Request zum Abrufen der Details
		$.ajax({
			url: meAccessibilityAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'me_get_scan_details',
				scan_id: scanId,
				nonce: meAccessibilityAdmin.scannerNonce
			},
			success: function(response) {
				$('#me-scan-issues-loading').hide();
				if (response.success && response.data.issues) {
					allIssues = response.data.issues;
					calculateAndRenderSummary();
					renderIssues();
				} else {
					$('#me-scan-issues-body').html('<tr><td colspan="3" style="text-align:center; color:#ef4444;">' + (response.data.message || 'Fehler beim Laden der Details.') + '</td></tr>');
				}
			},
			error: function() {
				$('#me-scan-issues-loading').hide();
				$('#me-scan-issues-body').html('<tr><td colspan="3" style="text-align:center; color:#ef4444;">Netzwerkfehler beim Laden der Details.</td></tr>');
			}
		});
	});

	// Schließen des Modals
	$(document).on('click', '.me-admin-modal-close, .me-admin-modal-close-btn', function(e) {
		e.preventDefault();
		$detailsModal.fadeOut(200);
		$('body').removeClass('me-modal-open');
	});

	// Klick außerhalb des Modals schließt es ebenfalls
	$detailsModal.on('click', function(e) {
		if ($(e.target).is($detailsModal)) {
			$detailsModal.fadeOut(200);
			$('body').removeClass('me-modal-open');
		}
	});

	// Filter-Buttons
	$('.me-filter-btn').on('click', function(e) {
		e.preventDefault();
		$('.me-filter-btn').removeClass('active');
		$(this).addClass('active');
		currentFilter = $(this).data('filter');
		renderIssues();
	});

	function calculateAndRenderSummary() {
		var critical = 0, warning = 0, notice = 0;
		allIssues.forEach(function(issue) {
			if (issue.severity === 'critical') critical++;
			else if (issue.severity === 'warning') warning++;
			else if (issue.severity === 'notice') notice++;
		});
		$('#me-critical-count').text(critical);
		$('#me-warning-count').text(warning);
		$('#me-notice-count').text(notice);
	}

	function renderIssues() {
		var $body = $('#me-scan-issues-body');
		$body.empty();

		var filtered = allIssues.filter(function(issue) {
			if (currentFilter === 'all') return true;
			return issue.severity === currentFilter;
		});

		if (filtered.length === 0) {
			$body.append('<tr><td colspan="3" style="text-align:center; padding:30px; color:#64748b;">' + meAccessibilityAdmin.translations.noIssuesFound + '</td></tr>');
			return;
		}

		filtered.forEach(function(issue) {
			var badgeClass = 'me-badge-notice';
			var badgeText = meAccessibilityAdmin.translations.notice;
			if (issue.severity === 'critical') {
				badgeClass = 'me-badge-critical';
				badgeText = meAccessibilityAdmin.translations.critical;
			} else if (issue.severity === 'warning') {
				badgeClass = 'me-badge-warning';
				badgeText = meAccessibilityAdmin.translations.warning;
			}

			var escapedSelector = $('<div/>').text(issue.selector).html();
			var escapedSnippet = $('<div/>').text(issue.code_snippet).html();

			var rowHtml = '<tr>';
			rowHtml += '<td><span class="me-issue-badge ' + badgeClass + '">' + badgeText + '</span></td>';
			rowHtml += '<td><strong>' + $('<div/>').text(issue.issue_type_label || issue.issue_type).html() + '</strong></td>';
			rowHtml += '<td>';
			rowHtml += '  <div class="me-issue-message">' + $('<div/>').text(issue.message).html() + '</div>';
			rowHtml += '  <div class="me-issue-meta">';
			rowHtml += '    <span><strong>Selector:</strong> <code>' + escapedSelector + '</code></span>';
			rowHtml += '  </div>';
			if (issue.code_snippet) {
				rowHtml += '  <pre class="me-issue-snippet"><code>' + escapedSnippet + '</code></pre>';
			}
			rowHtml += '</td>';
			rowHtml += '</tr>';

			$body.append(rowHtml);
		});
	}

	// ==========================================
	// 5. ONE CLICK AUTO FIX ACTIONS
	// ==========================================
	$(document).on('click', '.me-trigger-autofix-btn', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var originalText = $btn.text();

		$btn.prop('disabled', true).text(meAccessibilityAdmin.translations.applyingFixes || 'Korrigiere Fehler...');

		$.ajax({
			url: meAccessibilityAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'me_autofix_apply',
				nonce: meAccessibilityAdmin.scannerNonce
			},
			success: function(response) {
				if (response.success) {
					alert(response.data.message || meAccessibilityAdmin.translations.autofixSuccess || 'Auto-Fix erfolgreich abgeschlossen!');
					window.location.reload();
				} else {
					alert(response.data.message || meAccessibilityAdmin.translations.autofixError || 'Fehler beim Anwenden der Auto-Korrektur.');
					$btn.prop('disabled', false).text(originalText);
				}
			},
			error: function() {
				alert(meAccessibilityAdmin.translations.autofixNetworkError || 'Netzwerkfehler während der Auto-Korrektur.');
				$btn.prop('disabled', false).text(originalText);
			}
		});
	});

	$(document).on('click', '.me-trigger-rollback-btn', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var originalText = $btn.text();

		var confirmMsg = meAccessibilityAdmin.translations.confirmRollback || 'Möchten Sie alle automatischen Barrierefreiheits-Korrekturen wirklich rückgängig machen? Dies stellt die ursprünglichen leeren Medien-Alternativtexte wieder her.';
		if (!confirm(confirmMsg)) {
			return;
		}

		$btn.prop('disabled', true).text(meAccessibilityAdmin.translations.rollingBack || 'Rolle zurück...');

		$.ajax({
			url: meAccessibilityAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'me_autofix_rollback',
				nonce: meAccessibilityAdmin.scannerNonce
			},
			success: function(response) {
				if (response.success) {
					alert(response.data.message || meAccessibilityAdmin.translations.rollbackSuccess || 'Rollback erfolgreich durchgeführt!');
					window.location.reload();
				} else {
					alert(response.data.message || meAccessibilityAdmin.translations.rollbackError || 'Fehler beim Rollback.');
					$btn.prop('disabled', false).text(originalText);
				}
			},
			error: function() {
				alert(meAccessibilityAdmin.translations.rollbackNetworkError || 'Netzwerkfehler während des Rollbacks.');
				$btn.prop('disabled', false).text(originalText);
			}
		});
	});

	// ==========================================
	// SETTINGS SYSTEM (Redesigned)
	// ==========================================
	
	// Sub-Tab Navigation
	function switchSubtab(subtab) {
		$('.me-settings-nav-item').removeClass('active');
		$('.me-settings-nav-item[data-subtab="' + subtab + '"]').addClass('active');
		
		$('.me-settings-panel').removeClass('active');
		$('#me-subtab-' + subtab).addClass('active');
		
		localStorage.setItem('me-active-subtab', subtab);
		
		// Special case: sync preview if switching to design
		if (subtab === 'design') {
			if (typeof MeTriggerPreview !== 'undefined') {
				MeTriggerPreview.syncPreviewFromSliders(MeTriggerPreview.currentDevice);
			}
		}
	}

	$(document).on('click', '.me-settings-nav-item', function(e) {
		e.preventDefault();
		var subtab = $(this).data('subtab');
		switchSubtab(subtab);
	});

	// Initialize active sub-tab
	var activeSubtab = localStorage.getItem('me-active-subtab') || 'general';
	switchSubtab(activeSubtab);

	// Settings Search Filter
	$(document).on('input', '#me-settings-search', function() {
		var query = $(this).val().toLowerCase().trim();
		
		$('.me-settings-panel').each(function() {
			var $panel = $(this);
			var $cards = $panel.find('.me-settings-card');
			var visibleCardsCount = 0;
			
			$cards.each(function() {
				var $card = $(this);
				if (query === '') {
					$card.show();
					visibleCardsCount++;
				} else {
					var searchKeys = ($card.attr('data-search-keys') || '').toLowerCase();
					var cardText = $card.text().toLowerCase();
					
					if (searchKeys.indexOf(query) !== -1 || cardText.indexOf(query) !== -1) {
						$card.show();
						visibleCardsCount++;
					} else {
						$card.hide();
					}
				}
			});
			
			var $noResults = $panel.find('.me-search-no-results');
			if (visibleCardsCount === 0) {
				if ($noResults.length === 0) {
					$panel.append('<div class="me-search-no-results" style="padding: 30px; text-align: center; color: #64748b;">' + (meAccessibilityAdmin.translations.noSettingsFound || 'Keine passenden Einstellungen gefunden.') + '</div>');
				} else {
					$noResults.show();
				}
			} else {
				$noResults.hide();
			}
		});
	});

	// Reset to Defaults
	$(document).on('click', '#me-settings-reset-btn', function(e) {
		e.preventDefault();
		if (confirm(meAccessibilityAdmin.translations.confirmReset)) {
			// Reset General fields
			$('#me-lang-default').val('auto');
			$('#me-lang-force').val('');
			$('input[name="me_accessibility_settings[language][tts_multilang]"]').prop('checked', true);
			$('input[name="me_accessibility_settings[language][rtl_support]"]').prop('checked', true);
			$('#me-allowed-role').val('manage_options');
			$('input[name="me_accessibility_settings[disable_logged_in]"]').prop('checked', false);
			$('input[name="me_accessibility_settings[disable_mobile]"]').prop('checked', false);
			
			// Reset Widget Design fields
			$('#me-setting-accent-color').val('#2563eb');
			$('input[name="me_accessibility_settings[hide_branding]"]').prop('checked', false);
			$('#me-toolbar-icon-type').val('default').trigger('change');
			$('textarea[name="me_accessibility_settings[toolbar][custom_svg]"]').val('');
			$('#me-trigger-style').val('button');
			$('#me-toolbar-theme').val('light');
			$('#me-toolbar-position').val('right-bottom');
			
			// Reset Device positioning sliders & outputs
			var defaults = {
				desktop: { icon_size: 56, offset_bottom: 20, offset_side: 20 },
				tablet: { icon_size: 50, offset_bottom: 16, offset_side: 16 },
				mobile: { icon_size: 44, offset_bottom: 12, offset_side: 12 }
			};
			
			$.each(defaults, function(device, props) {
				$.each(props, function(prop, val) {
					var $slider = $('#me-slider-' + device + '-' + prop);
					$slider.val(val);
					$('#' + $slider.data('output')).text(val);
				});
			});
			
			// Reset Advanced fields
			$('textarea[name="me_accessibility_settings[custom_css]"]').val('');
			$('textarea[name="me_accessibility_settings[custom_js]"]').val('');
			
			// Sync Live Preview
			if (typeof MeTriggerPreview !== 'undefined') {
				MeTriggerPreview.updateIcon('default');
				$('#me-preview-trigger-btn').css('background-color', '#2563eb');
				MeTriggerPreview.syncPreviewFromSliders(MeTriggerPreview.currentDevice);
			}
		}
	});

	// Clear Cache
	$(document).on('click', '#me-clear-cache-btn', function(e) {
		e.preventDefault();
		var $btn = $(this);
		var originalHtml = $btn.html();
		
		$btn.prop('disabled', true).html('<span class="dashicons dashicons-update me-spin" style="margin-top:4px;"></span> ' + (meAccessibilityAdmin.translations.clearingCache || 'Clearing...'));
		
		$.ajax({
			url: meAccessibilityAdmin.ajaxUrl,
			type: 'POST',
			data: {
				action: 'me_clear_cache',
				nonce: meAccessibilityAdmin.scannerNonce
			},
			success: function(response) {
				$btn.prop('disabled', false).html(originalHtml);
				if (response.success) {
					alert(response.data.message || meAccessibilityAdmin.translations.cacheCleared);
				} else {
					alert(response.data.message || 'Error clearing cache.');
				}
			},
			error: function() {
				$btn.prop('disabled', false).html(originalHtml);
				alert(meAccessibilityAdmin.translations.networkError || 'Network error.');
			}
		});
	});

	// Live Preview hooks for accent color & trigger style
	$(document).on('input change', '#me-setting-accent-color', function() {
		$('#me-preview-trigger-btn').css('background-color', $(this).val());
	});

	$(document).on('change', '#me-trigger-style', function() {
		if (typeof MeTriggerPreview !== 'undefined') {
			MeTriggerPreview.syncPreviewFromSliders(MeTriggerPreview.currentDevice);
		}
	});

	// Hide/Show custom SVG textarea based on selected icon type
	$(document).on('change', '#me-toolbar-icon-type', function() {
		if ($(this).val() === 'custom') {
			$('#me-custom-svg-row').show();
		} else {
			$('#me-custom-svg-row').hide();
		}
	});

});

// ==========================================
// ME TRIGGER PREVIEW & DEVICE SLIDERS
// ==========================================
jQuery(document).ready(function($) {
	'use strict';

	// Update preview icon on icon type change
	$(document).on('change', '#me-toolbar-icon-type', function() {
		if (typeof MeTriggerPreview !== 'undefined') MeTriggerPreview.updateIcon($(this).val());
	});

	var MeTriggerPreview = window.MeTriggerPreview = {
		currentDevice: 'desktop',
		icons: {
			'default':    '<circle cx="12" cy="12" r="10"></circle><path d="M12 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm-4 6V8h8v6M10 20v-6h4v6"></path>',
			'eye':        '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>',
			'wheelchair': '<circle cx="12" cy="4" r="2"></circle><path d="M16 17a4 4 0 1 1-8 0"></path>',
			'gear':       '<circle cx="12" cy="12" r="3"></circle>',
			'custom':     '<circle cx="12" cy="12" r="10"></circle>'
		},
		init: function() {
			var self = this;
			$(document).on('click', '.me-device-tab', function() { self.switchDevice($(this).data('device')); });
			$(document).on('click', '.me-preview-size-btn', function() {
				$('.me-preview-size-btn').removeClass('me-preview-size-active');
				$(this).addClass('me-preview-size-active');
				self.resizePreviewFrame($(this).data('preview-size'));
			});
			$(document).on('input change', '.me-range-slider', function() {
				$('#' + $(this).data('output')).text($(this).val());
				self.updatePreview($(this).data('device'));
			});
			self.syncPreviewFromSliders('desktop');
		},
		switchDevice: function(device) {
			this.currentDevice = device;
			$('.me-device-tab').removeClass('me-device-tab-active');
			$('.me-device-tab[data-device="' + device + '"]').addClass('me-device-tab-active');
			$('.me-device-panel').hide();
			$('#me-panel-' + device).show();
			this.syncPreviewFromSliders(device);
		},
		syncPreviewFromSliders: function(device) {
			var iconSize     = parseInt($('#me-slider-' + device + '-icon_size').val(), 10)     || 56;
			var offsetBottom = parseInt($('#me-slider-' + device + '-offset_bottom').val(), 10) || 20;
			var offsetSide   = parseInt($('#me-slider-' + device + '-offset_side').val(), 10)   || 20;
			this.applyToPreview(iconSize, offsetBottom, offsetSide);
		},
		updatePreview: function(device) {
			if (device !== this.currentDevice) return;
			this.syncPreviewFromSliders(device);
		},
		applyToPreview: function(iconSize, offsetBottom, offsetSide) {
			var $trigger = $('#me-preview-trigger-btn');
			var $screen  = $('#me-preview-screen');
			var screenW  = $screen.width()  || 280;
			var screenH  = $screen.height() || 180;
			var pSize   = Math.max(14, Math.round(iconSize * (screenW / 1280)));
			var pBottom = Math.round(offsetBottom * (screenH / 800));
			var pSide   = Math.round(offsetSide * (screenW / 1280));
			
			var isBadge = $('#me-trigger-style').val() === 'badge';
			
			if (isBadge) {
				$trigger.css({
					width: 'auto',
					height: pSize+'px',
					bottom: pBottom+'px',
					right: pSide+'px',
					borderRadius: Math.round(pSize / 2) + 'px',
					padding: '0 ' + Math.max(4, Math.round(pSize * 0.2)) + 'px',
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'center'
				});
				$('#me-preview-trigger-text').show();
			} else {
				$trigger.css({
					width: pSize+'px',
					height: pSize+'px',
					bottom: pBottom+'px',
					right: pSide+'px',
					borderRadius: '50%',
					padding: '0',
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'center'
				});
				$('#me-preview-trigger-text').hide();
			}
			$trigger.find('svg').css({ width: Math.round(pSize*0.52)+'px', height: Math.round(pSize*0.52)+'px' });
		},
		updateIcon: function(iconType) {
			var paths = this.icons[iconType] || this.icons['default'];
			$('#me-preview-icon').html(paths);
		},
		resizePreviewFrame: function(size) {
			var $frame = $('#me-preview-device-frame');
			$frame.removeClass('me-preview-desktop me-preview-tablet me-preview-mobile').addClass('me-preview-' + size);
			var self = this;
			setTimeout(function() { self.syncPreviewFromSliders(self.currentDevice); }, 150);
		}
	};

	MeTriggerPreview.init();
});
