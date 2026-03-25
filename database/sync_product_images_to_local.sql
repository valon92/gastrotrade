-- Sync image paths on live DB to match localhost seeder values.
-- Run this in phpMyAdmin on cPanel (target DB: arontrade).

START TRANSACTION;

UPDATE products
SET image_path = CASE slug
  WHEN 'luga-kafe-e-bardh-10cp-kompleti' THEN '/images/lugas1.jpeg'
  WHEN 'pipeza-plastike-te-zeza' THEN '/images/IMG_6821.jpeg'
  WHEN 'folie-alumini-9-micron' THEN '/images/IMG_6814.jpeg'
  WHEN 'leter-kuzhine-nush-2-shtresa' THEN '/images/Pallomat/XL/foto1.png'
  WHEN 'leter-salvete' THEN '/images/Pallomat/Salvete/foto1.jpg'
  WHEN 'baby-wipes-canea-aqua-pure' THEN '/images/IMG_6818.jpeg'
  WHEN 'pipeza-transparente-per-pije' THEN '/images/IMG_6819.jpeg'
  WHEN 'kese-mbeturinash-40l' THEN '/images/IMG_6815.jpeg'
  WHEN 'kese-mbeturinash-70l' THEN '/images/IMG_6815.jpeg'
  WHEN 'kese-mbeturinash-120l' THEN '/images/120nush.jpeg'
  WHEN 'kese-mbeturinash-150l' THEN '/images/150nush.jpeg'
  WHEN 'kese-mbeturinash-170l' THEN '/images/170lnush.jpeg'
  WHEN 'kese-mbeturinash-200l' THEN '/images/200nush.jpeg'
  WHEN 'kese-mbeturinash-240l' THEN '/images/IMG_6815.jpeg'
  WHEN 'kese-mbeturinash-270l' THEN '/images/IMG_6815.jpeg'
  WHEN 'kese-mbeturinash-300l' THEN '/images/IMG_6815.jpeg'
  WHEN 'kese-mbeturinash-70l-me-lidhse' THEN '/images/IMG_6815.jpeg'
  WHEN 'kese-mbeturinash-120l-me-lidhse' THEN '/images/IMG_6815.jpeg'
  WHEN 'kese-mbeturinash-200l-me-lidhse' THEN '/images/IMG_6815.jpeg'
  WHEN 'kese-mbeturinash-220l-me-lidhse' THEN '/images/IMG_6815.jpeg'
  WHEN 'pipeza-me-ngjyra-te-ndryshme' THEN '/images/IMG_6816.jpeg'
  WHEN 'mbulese-tavoline-ldp' THEN '/images/IMG_6817.jpeg'
  WHEN 'gota-leter-per-kafe-2-5oz' THEN '/images/IMG_6822.jpeg'
  WHEN 'gota-leter-per-kafe-3oz' THEN '/images/IMG_6823.jpeg'
  WHEN 'gota-leter-per-kafe-4oz' THEN '/images/4oz.jpeg'
  WHEN 'gota-leter-per-kafe-7oz' THEN '/images/IMG_6825.jpeg'
  WHEN 'gota-plastike' THEN '/images/gotplastike.jpeg'
  WHEN 'produkt-shtese-1' THEN '/images/IMG_6821.jpeg'
  WHEN 'produkt-shtese-2' THEN '/images/IMG_6821.jpeg'
  WHEN 'produkt-shtese-3' THEN '/images/IMG_6821.jpeg'
  ELSE image_path
END,
updated_at = NOW()
WHERE slug IN (
  'luga-kafe-e-bardh-10cp-kompleti',
  'pipeza-plastike-te-zeza',
  'folie-alumini-9-micron',
  'leter-kuzhine-nush-2-shtresa',
  'leter-salvete',
  'baby-wipes-canea-aqua-pure',
  'pipeza-transparente-per-pije',
  'kese-mbeturinash-40l',
  'kese-mbeturinash-70l',
  'kese-mbeturinash-120l',
  'kese-mbeturinash-150l',
  'kese-mbeturinash-170l',
  'kese-mbeturinash-200l',
  'kese-mbeturinash-240l',
  'kese-mbeturinash-270l',
  'kese-mbeturinash-300l',
  'kese-mbeturinash-70l-me-lidhse',
  'kese-mbeturinash-120l-me-lidhse',
  'kese-mbeturinash-200l-me-lidhse',
  'kese-mbeturinash-220l-me-lidhse',
  'pipeza-me-ngjyra-te-ndryshme',
  'mbulese-tavoline-ldp',
  'gota-leter-per-kafe-2-5oz',
  'gota-leter-per-kafe-3oz',
  'gota-leter-per-kafe-4oz',
  'gota-leter-per-kafe-7oz',
  'gota-plastike',
  'produkt-shtese-1',
  'produkt-shtese-2',
  'produkt-shtese-3'
);

-- Keep product_images featured record in sync with products.image_path.
UPDATE product_images pi
JOIN products p ON p.id = pi.product_id
SET pi.file_path = p.image_path,
    pi.file_name = SUBSTRING_INDEX(p.image_path, '/', -1),
    pi.updated_at = NOW()
WHERE pi.is_featured = 1;

INSERT INTO product_images (product_id, file_name, file_path, is_featured, sort_order, created_at, updated_at)
SELECT p.id, SUBSTRING_INDEX(p.image_path, '/', -1), p.image_path, 1, 0, NOW(), NOW()
FROM products p
LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_featured = 1
WHERE p.image_path IS NOT NULL
  AND p.image_path <> ''
  AND pi.id IS NULL;

COMMIT;
