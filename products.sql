--
-- Tablo için tablo yapısı `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `parentID` varchar(70) COLLATE utf8mb3_turkish_ci NOT NULL,
  `mast_cat_id` varchar(70) COLLATE utf8mb3_turkish_ci DEFAULT NULL,
  `top_cat_id` varchar(200) COLLATE utf8mb3_turkish_ci DEFAULT NULL,
  `sub_cat_id` varchar(70) COLLATE utf8mb3_turkish_ci DEFAULT NULL,
  `bottom_cat_id` varchar(80) COLLATE utf8mb3_turkish_ci DEFAULT NULL,
  `title` varchar(999) COLLATE utf8mb3_turkish_ci NOT NULL,
  `img` varchar(999) COLLATE utf8mb3_turkish_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb3_turkish_ci NOT NULL,
  `dimensions` varchar(500) COLLATE utf8mb3_turkish_ci DEFAULT NULL,
  `slug` varchar(999) COLLATE utf8mb3_turkish_ci NOT NULL,
  `view` varchar(999) COLLATE utf8mb3_turkish_ci NOT NULL,
  `url` varchar(999) COLLATE utf8mb3_turkish_ci NOT NULL,
  `status` tinyint NOT NULL,
  `createdAt` varchar(999) COLLATE utf8mb3_turkish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_turkish_ci;

--
-- Tablo döküm verisi `products`
--

INSERT INTO `products` (`id`, `parentID`, `mast_cat_id`, `top_cat_id`, `sub_cat_id`, `bottom_cat_id`, `title`, `img`, `description`, `dimensions`, `slug`, `view`, `url`, `status`, `createdAt`) VALUES
(60, 'r3wjX61n2N8maPssG5x6', 'erL2R2pp5UYee196w4th', '2kx9rhjwz58pDbba339V', 'IM98KV2LB5Hn5chTq73W', 'oV72dB2Z8Y9Hlb96Vuc0', 'Kat Servis Arabası-2704', 'ac2704.jpg', '<p>PP plastik malzemeden imal edilmiş servis arabası, &ouml;zellikle dar alanlarda kullanım kolaylığı sağlamaktadır. D&ouml;rt hareketli tekerleğin ikisi frenlidir, calisma esnasında sabitleme imkanı sunmaktadır. &Uuml;r&uuml;n&uuml;n t&uuml;m par&ccedil;alarının yedekleri bulunmaktadır, dolayısı ile olası hasarlarda, hızla tedarik edilerek işinizi aksatmadan devam etmenize olanak sağlar.</p>\r\n\r\n<p>GTIP:&nbsp;9403.70.00.00.19<br />\r\nUrun demonte şekilde g&ouml;nderilmektedir, kurulumu basittir.</p>\r\n\r\n<p>Koli i&ccedil;i: 1 adet</p>\r\n', '62X57X145 cm', 'tr', 'Türkçe', 'kat-servis-arabasi-2704', 1, '25-09-2023 16:14:58'),
(61, 'r3wjX61n2N8maPssG5x6', 'erL2R2pp5UYee196w4th', '2kx9rhjwz58pDbba339V', 'IM98KV2LB5Hn5chTq73W', 'oV72dB2Z8Y9Hlb96Vuc0', 'Floor Service Trolley-2704', 'ac2704.jpg', '<p>The service trolley made of PP plastic material provides ease of use, especially in narrow spaces. Two of the four movable wheels are braked, providing stabilization during operation. All parts of the product have spares, so in case of possible damage, it can be supplied quickly and allows you to continue your work without interruption.</p>\r\n\r\n<p>GTIP: 9403.70.00.00.19<br />\r\nThe product is shipped disassembled, installation is simple.</p>\r\n\r\n<p>Package includes: 1 piece</p>\r\n', '62X57X145 cm', 'en', 'English', 'floor-service-trolley-2704', 1, '25-09-2023 16:14:58'),
(62, 'r3wjX61n2N8maPssG5x6', 'erL2R2pp5UYee196w4th', '2kx9rhjwz58pDbba339V', 'IM98KV2LB5Hn5chTq73W', 'oV72dB2Z8Y9Hlb96Vuc0', 'Тележка для обслуживания пола-2704', 'ac2704.jpg', '<p>Сервисная тележка, изготовленная из полипропиленового пластика, обеспечивает удобство использования, особенно в узких местах. Два из четырех подвижных колес имеют тормоз, что обеспечивает стабилизацию во время работы. Все детали изделия имеют запасные части, поэтому в случае возможной поломки она может быть быстро поставлена и позволит продолжить работу без перерыва.</p>\r\n\r\n<p>GTIP: 9403.70.00.00.19<br />\r\nИзделие поставляется в разобранном виде, монтаж прост.</p>\r\n\r\n<p>Комплект поставки включает: 1 шт.</p>\r\n', '62X57X145 см', 'ru', 'Russian', 'Тележка-для-обслуживания-пола-2704', 1, '25-09-2023 16:14:58'),
(63, 'r3wjX61n2N8maPssG5x6', 'erL2R2pp5UYee196w4th', '2kx9rhjwz58pDbba339V', 'IM98KV2LB5Hn5chTq73W', 'oV72dB2Z8Y9Hlb96Vuc0', 'عربة خدمة الأرضية-2704', 'ac2704.jpg', '<p>توفر عربة الخدمة المصنوعة من مادة بلاستيك PP سهولة الاستخدام، خاصة في المساحات الضيقة. يتم فرامل اثنتين من العجلات الأربع المتحركة، مما يوفر الاستقرار أثناء التشغيل. تحتوي جميع أجزاء المنتج على قطع غيار، لذلك في حالة حدوث ضرر محتمل، يمكن توفيرها بسرعة وتسمح لك بمواصلة عملك دون انقطاع.</p>\r\n\r\n<p>GTIP: 9403.70.00.00.19<br />\r\nيتم شحن المنتج مفككًا، والتركيب بسيط.</p>\r\n\r\n<p>تتضمن العبوة: قطعة واحدة</p>\r\n', '62×57×145 سم', 'ar', 'Arabic', 'عربة-خدمة-الأرضية-2704', 1, '25-09-2023 16:14:58'),