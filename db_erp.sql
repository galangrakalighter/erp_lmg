/*
 Navicat Premium Data Transfer

 Source Server         : db
 Source Server Type    : PostgreSQL
 Source Server Version : 180001 (180001)
 Source Host           : 127.0.0.1:5432
 Source Catalog        : lighter1_kontenmanajemen
 Source Schema         : public

 Target Server Type    : PostgreSQL
 Target Server Version : 180001 (180001)
 File Encoding         : 65001

 Date: 19/01/2026 15:43:16
*/


-- ----------------------------
-- Sequence structure for alat_branding_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."alat_branding_id_seq";
CREATE SEQUENCE "public"."alat_branding_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for analize_4p_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."analize_4p_id_seq";
CREATE SEQUENCE "public"."analize_4p_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for analize_swot_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."analize_swot_id_seq";
CREATE SEQUENCE "public"."analize_swot_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for audio_brand_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."audio_brand_id_seq";
CREATE SEQUENCE "public"."audio_brand_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for brand_identify_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."brand_identify_id_seq";
CREATE SEQUENCE "public"."brand_identify_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for brand_image_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."brand_image_id_seq";
CREATE SEQUENCE "public"."brand_image_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for brand_tagline_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."brand_tagline_id_seq";
CREATE SEQUENCE "public"."brand_tagline_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for brand_value_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."brand_value_id_seq";
CREATE SEQUENCE "public"."brand_value_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for data_jasa_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."data_jasa_id_seq";
CREATE SEQUENCE "public"."data_jasa_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for failed_jobs_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."failed_jobs_id_seq";
CREATE SEQUENCE "public"."failed_jobs_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for gaya_komunikasi_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."gaya_komunikasi_id_seq";
CREATE SEQUENCE "public"."gaya_komunikasi_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for hooks_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."hooks_id_seq";
CREATE SEQUENCE "public"."hooks_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for jenis_body_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."jenis_body_id_seq";
CREATE SEQUENCE "public"."jenis_body_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for jenis_cta_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."jenis_cta_id_seq";
CREATE SEQUENCE "public"."jenis_cta_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for jobs_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."jobs_id_seq";
CREATE SEQUENCE "public"."jobs_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for kategori_jasa_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."kategori_jasa_id_seq";
CREATE SEQUENCE "public"."kategori_jasa_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for komposisi_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."komposisi_id_seq";
CREATE SEQUENCE "public"."komposisi_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for migrations_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."migrations_id_seq";
CREATE SEQUENCE "public"."migrations_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 2147483647
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for moodboard_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."moodboard_id_seq";
CREATE SEQUENCE "public"."moodboard_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for personal_access_tokens_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."personal_access_tokens_id_seq";
CREATE SEQUENCE "public"."personal_access_tokens_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for pilar_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."pilar_id_seq";
CREATE SEQUENCE "public"."pilar_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for positioning_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."positioning_id_seq";
CREATE SEQUENCE "public"."positioning_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for produk_client_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."produk_client_id_seq";
CREATE SEQUENCE "public"."produk_client_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for produk_jasa_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."produk_jasa_id_seq";
CREATE SEQUENCE "public"."produk_jasa_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for segmentasi_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."segmentasi_id_seq";
CREATE SEQUENCE "public"."segmentasi_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for status_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."status_id_seq";
CREATE SEQUENCE "public"."status_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for strategy_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."strategy_id_seq";
CREATE SEQUENCE "public"."strategy_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for target_audience_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."target_audience_id_seq";
CREATE SEQUENCE "public"."target_audience_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for type_produk_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."type_produk_id_seq";
CREATE SEQUENCE "public"."type_produk_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Sequence structure for users_id_seq
-- ----------------------------
DROP SEQUENCE IF EXISTS "public"."users_id_seq";
CREATE SEQUENCE "public"."users_id_seq" 
INCREMENT 1
MINVALUE  1
MAXVALUE 9223372036854775807
START 1
CACHE 1;

-- ----------------------------
-- Table structure for alat_branding
-- ----------------------------
DROP TABLE IF EXISTS "public"."alat_branding";
CREATE TABLE "public"."alat_branding" (
  "id" int8 NOT NULL DEFAULT nextval('alat_branding_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "nama_alat" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "platform" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of alat_branding
-- ----------------------------

-- ----------------------------
-- Table structure for analize_4p
-- ----------------------------
DROP TABLE IF EXISTS "public"."analize_4p";
CREATE TABLE "public"."analize_4p" (
  "id" int8 NOT NULL DEFAULT nextval('analize_4p_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "nama_analisis" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "type" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "platform" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of analize_4p
-- ----------------------------
INSERT INTO "public"."analize_4p" VALUES (2, 1, 'Pilihan menu Nusantara, Asian, dan Western dalam satu tempat.', 'Product', 'Instagram', NULL, NULL);
INSERT INTO "public"."analize_4p" VALUES (3, 1, 'Minuman segar non kopi dan kopi ringan untuk semua tipe tamu.', 'Product', 'Instagram', NULL, NULL);
INSERT INTO "public"."analize_4p" VALUES (4, 1, 'Menu mudah dipahami dan aman untuk first timer.', 'Product', 'Instagram', NULL, NULL);
INSERT INTO "public"."analize_4p" VALUES (5, 1, 'Cocok untuk nongkrong, gathering kecil, dan acara santai.', 'Product', 'Instagram', NULL, NULL);
INSERT INTO "public"."analize_4p" VALUES (6, 1, 'Harga makanan utama 45k sampai 85k. Masuk akal untuk makan kenyang.', 'Price', 'Instagram', NULL, NULL);
INSERT INTO "public"."analize_4p" VALUES (7, 1, 'Harga camilan 25k sampai 45k. Ideal untuk pesan beberapa item.', 'Price', 'Instagram', NULL, NULL);
INSERT INTO "public"."analize_4p" VALUES (8, 1, 'Harga minuman 25k sampai 35k. Tidak membebani saat nongkrong lama.', 'Price', 'Instagram', NULL, NULL);
INSERT INTO "public"."analize_4p" VALUES (9, 1, 'Ruang nyaman untuk duduk lama dan kumpul rame rame.', 'Place', 'Instagram', NULL, NULL);
INSERT INTO "public"."analize_4p" VALUES (10, 1, 'Cocok untuk pengunjung lokal dan luar kota.', 'Place', 'Instagram', NULL, NULL);
INSERT INTO "public"."analize_4p" VALUES (11, 1, 'Mendukung dine in sebagai aktivitas utama.', 'Place', 'Instagram', NULL, NULL);
INSERT INTO "public"."analize_4p" VALUES (12, 1, 'Konten fokus ke momen nongkrong dan kumpul bareng.', 'Promotion', 'Instagram', NULL, NULL);
INSERT INTO "public"."analize_4p" VALUES (13, 1, 'Tonjolkan harga aman untuk nongkrong rame rame.', 'Promotion', 'Instagram', NULL, NULL);
INSERT INTO "public"."analize_4p" VALUES (14, 1, 'Menggunakan Instagram sebagai channel utama.', 'Promotion', 'Instagram', NULL, NULL);
INSERT INTO "public"."analize_4p" VALUES (15, 1, 'Dorong reservasi untuk birthday, gathering, dan komunitas.', 'Promotion', 'Instagram', NULL, NULL);

-- ----------------------------
-- Table structure for analize_swot
-- ----------------------------
DROP TABLE IF EXISTS "public"."analize_swot";
CREATE TABLE "public"."analize_swot" (
  "id" int8 NOT NULL DEFAULT nextval('analize_swot_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "strenght" varchar(255) COLLATE "pg_catalog"."default",
  "weakness" varchar(255) COLLATE "pg_catalog"."default",
  "oportunity" varchar(255) COLLATE "pg_catalog"."default",
  "threat" varchar(255) COLLATE "pg_catalog"."default",
  "platform" varchar(255) COLLATE "pg_catalog"."default",
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of analize_swot
-- ----------------------------
INSERT INTO "public"."analize_swot" VALUES (1, 1, 'Menu mulai dari makanan berat sampai camilan.', 'Menu sebagian besar familiar dan mudah ditiru.', 'Tren nongkrong lama dan kumpul santai terus meningkat.', 'Banyak cafe dan restoran serupa di Bandung.', 'Instagram', '2026-01-19 06:41:27', '2026-01-19 06:41:27');
INSERT INTO "public"."analize_swot" VALUES (4, 1, 'Harga terjangkau untuk nongkrong rame rame.', 'Belum ada paket menu khusus nongkrong rame rame.', 'Kebutuhan tempat kumpul komunitas dan acara kecil tinggi.', 'Persaingan harga dengan tempat nongkrong lain.', 'Instagram', '2026-01-19 06:55:53', '2026-01-19 06:55:53');
INSERT INTO "public"."analize_swot" VALUES (5, 1, 'Tempat mendukung durasi duduk lama.', 'Menu minuman masih terbatas dan belum jadi daya tarik utama.', 'Potensi paket sharing dan promo grup.', 'Tren kuliner cepat berubah.', 'Instagram', '2026-01-19 06:56:20', '2026-01-19 06:59:47');

-- ----------------------------
-- Table structure for audio_brand
-- ----------------------------
DROP TABLE IF EXISTS "public"."audio_brand";
CREATE TABLE "public"."audio_brand" (
  "id" int8 NOT NULL DEFAULT nextval('audio_brand_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "nama_audio" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "platform" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of audio_brand
-- ----------------------------

-- ----------------------------
-- Table structure for brand_identify
-- ----------------------------
DROP TABLE IF EXISTS "public"."brand_identify";
CREATE TABLE "public"."brand_identify" (
  "id" int8 NOT NULL DEFAULT nextval('brand_identify_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "identify" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "platform" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of brand_identify
-- ----------------------------

-- ----------------------------
-- Table structure for brand_image
-- ----------------------------
DROP TABLE IF EXISTS "public"."brand_image";
CREATE TABLE "public"."brand_image" (
  "id" int8 NOT NULL DEFAULT nextval('brand_image_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "nama_image" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "platform" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of brand_image
-- ----------------------------

-- ----------------------------
-- Table structure for brand_tagline
-- ----------------------------
DROP TABLE IF EXISTS "public"."brand_tagline";
CREATE TABLE "public"."brand_tagline" (
  "id" int8 NOT NULL DEFAULT nextval('brand_tagline_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "nama_tagline" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "nama_hashtagline" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "platform" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of brand_tagline
-- ----------------------------

-- ----------------------------
-- Table structure for brand_value
-- ----------------------------
DROP TABLE IF EXISTS "public"."brand_value";
CREATE TABLE "public"."brand_value" (
  "id" int8 NOT NULL DEFAULT nextval('brand_value_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "nama_value" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "platform" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of brand_value
-- ----------------------------

-- ----------------------------
-- Table structure for cache
-- ----------------------------
DROP TABLE IF EXISTS "public"."cache";
CREATE TABLE "public"."cache" (
  "key" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "value" text COLLATE "pg_catalog"."default" NOT NULL,
  "expiration" int4 NOT NULL
)
;

-- ----------------------------
-- Records of cache
-- ----------------------------

-- ----------------------------
-- Table structure for cache_locks
-- ----------------------------
DROP TABLE IF EXISTS "public"."cache_locks";
CREATE TABLE "public"."cache_locks" (
  "key" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "owner" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "expiration" int4 NOT NULL
)
;

-- ----------------------------
-- Records of cache_locks
-- ----------------------------

-- ----------------------------
-- Table structure for data_jasa
-- ----------------------------
DROP TABLE IF EXISTS "public"."data_jasa";
CREATE TABLE "public"."data_jasa" (
  "id" int8 NOT NULL DEFAULT nextval('data_jasa_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "judul" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "inspirasi" varchar(255) COLLATE "pg_catalog"."default",
  "type_produk_id" int8 NOT NULL,
  "strategy_id" int8 NOT NULL,
  "pilar_id" int8 NOT NULL,
  "hooks_id" int8 NOT NULL,
  "body_id" int8 NOT NULL,
  "cta_id" int8 NOT NULL,
  "durasi" varchar(255) COLLATE "pg_catalog"."default",
  "background" varchar(255) COLLATE "pg_catalog"."default",
  "komposisi" varchar(255) COLLATE "pg_catalog"."default",
  "note" text COLLATE "pg_catalog"."default",
  "tanggal_posting" date,
  "tanggal_deadline" date,
  "link" varchar(255) COLLATE "pg_catalog"."default",
  "platform" varchar(255) COLLATE "pg_catalog"."default",
  "status_id" int4 NOT NULL,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of data_jasa
-- ----------------------------

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS "public"."failed_jobs";
CREATE TABLE "public"."failed_jobs" (
  "id" int8 NOT NULL DEFAULT nextval('failed_jobs_id_seq'::regclass),
  "uuid" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "connection" text COLLATE "pg_catalog"."default" NOT NULL,
  "queue" text COLLATE "pg_catalog"."default" NOT NULL,
  "payload" text COLLATE "pg_catalog"."default" NOT NULL,
  "exception" text COLLATE "pg_catalog"."default" NOT NULL,
  "failed_at" timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP
)
;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for gaya_komunikasi
-- ----------------------------
DROP TABLE IF EXISTS "public"."gaya_komunikasi";
CREATE TABLE "public"."gaya_komunikasi" (
  "id" int8 NOT NULL DEFAULT nextval('gaya_komunikasi_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "gaya_bicara" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "gaya_bahasa" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "platform" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of gaya_komunikasi
-- ----------------------------

-- ----------------------------
-- Table structure for hooks
-- ----------------------------
DROP TABLE IF EXISTS "public"."hooks";
CREATE TABLE "public"."hooks" (
  "id" int8 NOT NULL DEFAULT nextval('hooks_id_seq'::regclass),
  "hooks" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "status" int4 NOT NULL,
  "kategori_id" int4,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of hooks
-- ----------------------------

-- ----------------------------
-- Table structure for jenis_body
-- ----------------------------
DROP TABLE IF EXISTS "public"."jenis_body";
CREATE TABLE "public"."jenis_body" (
  "id" int8 NOT NULL DEFAULT nextval('jenis_body_id_seq'::regclass),
  "nama_body" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "status" int4 NOT NULL,
  "kategori_id" int4,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of jenis_body
-- ----------------------------

-- ----------------------------
-- Table structure for jenis_cta
-- ----------------------------
DROP TABLE IF EXISTS "public"."jenis_cta";
CREATE TABLE "public"."jenis_cta" (
  "id" int8 NOT NULL DEFAULT nextval('jenis_cta_id_seq'::regclass),
  "nama_cta" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "status" int4 NOT NULL,
  "kategori_id" int4,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of jenis_cta
-- ----------------------------

-- ----------------------------
-- Table structure for job_batches
-- ----------------------------
DROP TABLE IF EXISTS "public"."job_batches";
CREATE TABLE "public"."job_batches" (
  "id" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "name" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "total_jobs" int4 NOT NULL,
  "pending_jobs" int4 NOT NULL,
  "failed_jobs" int4 NOT NULL,
  "failed_job_ids" text COLLATE "pg_catalog"."default" NOT NULL,
  "options" text COLLATE "pg_catalog"."default",
  "cancelled_at" int4,
  "created_at" int4 NOT NULL,
  "finished_at" int4
)
;

-- ----------------------------
-- Records of job_batches
-- ----------------------------

-- ----------------------------
-- Table structure for jobs
-- ----------------------------
DROP TABLE IF EXISTS "public"."jobs";
CREATE TABLE "public"."jobs" (
  "id" int8 NOT NULL DEFAULT nextval('jobs_id_seq'::regclass),
  "queue" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "payload" text COLLATE "pg_catalog"."default" NOT NULL,
  "attempts" int2 NOT NULL,
  "reserved_at" int4,
  "available_at" int4 NOT NULL,
  "created_at" int4 NOT NULL
)
;

-- ----------------------------
-- Records of jobs
-- ----------------------------

-- ----------------------------
-- Table structure for kategori_jasa
-- ----------------------------
DROP TABLE IF EXISTS "public"."kategori_jasa";
CREATE TABLE "public"."kategori_jasa" (
  "id" int8 NOT NULL DEFAULT nextval('kategori_jasa_id_seq'::regclass),
  "category" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of kategori_jasa
-- ----------------------------
INSERT INTO "public"."kategori_jasa" VALUES (1, 'Client', NULL, NULL);
INSERT INTO "public"."kategori_jasa" VALUES (2, 'Internal Jasa', NULL, NULL);
INSERT INTO "public"."kategori_jasa" VALUES (3, 'Internal Barang', NULL, NULL);

-- ----------------------------
-- Table structure for komposisi
-- ----------------------------
DROP TABLE IF EXISTS "public"."komposisi";
CREATE TABLE "public"."komposisi" (
  "id" int8 NOT NULL DEFAULT nextval('komposisi_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "type_komposisi" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "komposisi" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "platform" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of komposisi
-- ----------------------------

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS "public"."migrations";
CREATE TABLE "public"."migrations" (
  "id" int4 NOT NULL DEFAULT nextval('migrations_id_seq'::regclass),
  "migration" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "batch" int4 NOT NULL
)
;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO "public"."migrations" VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO "public"."migrations" VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO "public"."migrations" VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO "public"."migrations" VALUES (4, '2024_12_16_013507_kategori_jasa', 1);
INSERT INTO "public"."migrations" VALUES (5, '2025_12_16_013507_hooks', 1);
INSERT INTO "public"."migrations" VALUES (6, '2025_12_16_013507_jenis_body', 1);
INSERT INTO "public"."migrations" VALUES (7, '2025_12_16_013507_jenis_cta', 1);
INSERT INTO "public"."migrations" VALUES (8, '2025_12_16_013507_pilar', 1);
INSERT INTO "public"."migrations" VALUES (9, '2025_12_16_013507_status', 1);
INSERT INTO "public"."migrations" VALUES (10, '2025_12_16_013507_strategy', 1);
INSERT INTO "public"."migrations" VALUES (11, '2025_12_16_013507_type_produk', 1);
INSERT INTO "public"."migrations" VALUES (12, '2025_12_16_013514_produk_client', 1);
INSERT INTO "public"."migrations" VALUES (13, '2025_12_16_013534_produk_jasa', 1);
INSERT INTO "public"."migrations" VALUES (14, '2025_12_16_015843_data_jasa', 1);
INSERT INTO "public"."migrations" VALUES (15, '2025_12_17_080209_create_personal_access_tokens_table', 1);
INSERT INTO "public"."migrations" VALUES (16, '2025_12_24_011333_analize_4p', 1);
INSERT INTO "public"."migrations" VALUES (17, '2025_12_24_011351_analize_swot', 1);
INSERT INTO "public"."migrations" VALUES (18, '2025_12_24_011430_segmentasi', 1);
INSERT INTO "public"."migrations" VALUES (19, '2025_12_24_011442_target_audience', 1);
INSERT INTO "public"."migrations" VALUES (20, '2025_12_24_011449_position', 1);
INSERT INTO "public"."migrations" VALUES (21, '2025_12_26_063711_brand_identify', 1);
INSERT INTO "public"."migrations" VALUES (22, '2025_12_26_063717_brand_image', 1);
INSERT INTO "public"."migrations" VALUES (23, '2025_12_26_063722_brand_value', 1);
INSERT INTO "public"."migrations" VALUES (24, '2025_12_26_063737_brand_tagline', 1);
INSERT INTO "public"."migrations" VALUES (25, '2025_12_26_063756_brand_bicara', 1);
INSERT INTO "public"."migrations" VALUES (26, '2025_12_26_063809_komposisi', 1);
INSERT INTO "public"."migrations" VALUES (27, '2025_12_26_063820_audio_brand', 1);
INSERT INTO "public"."migrations" VALUES (28, '2025_12_26_063830_moodboard', 1);
INSERT INTO "public"."migrations" VALUES (29, '2025_12_26_063839_alat_branding', 1);

-- ----------------------------
-- Table structure for moodboard
-- ----------------------------
DROP TABLE IF EXISTS "public"."moodboard";
CREATE TABLE "public"."moodboard" (
  "id" int8 NOT NULL DEFAULT nextval('moodboard_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "moodboard" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "platform" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of moodboard
-- ----------------------------

-- ----------------------------
-- Table structure for password_reset_tokens
-- ----------------------------
DROP TABLE IF EXISTS "public"."password_reset_tokens";
CREATE TABLE "public"."password_reset_tokens" (
  "email" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "token" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(0)
)
;

-- ----------------------------
-- Records of password_reset_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for personal_access_tokens
-- ----------------------------
DROP TABLE IF EXISTS "public"."personal_access_tokens";
CREATE TABLE "public"."personal_access_tokens" (
  "id" int8 NOT NULL DEFAULT nextval('personal_access_tokens_id_seq'::regclass),
  "tokenable_type" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "tokenable_id" int8 NOT NULL,
  "name" text COLLATE "pg_catalog"."default" NOT NULL,
  "token" varchar(64) COLLATE "pg_catalog"."default" NOT NULL,
  "abilities" text COLLATE "pg_catalog"."default",
  "last_used_at" timestamp(0),
  "expires_at" timestamp(0),
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of personal_access_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for pilar
-- ----------------------------
DROP TABLE IF EXISTS "public"."pilar";
CREATE TABLE "public"."pilar" (
  "id" int8 NOT NULL DEFAULT nextval('pilar_id_seq'::regclass),
  "pilar" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "status" int4 NOT NULL,
  "kategori_id" int4,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of pilar
-- ----------------------------

-- ----------------------------
-- Table structure for positioning
-- ----------------------------
DROP TABLE IF EXISTS "public"."positioning";
CREATE TABLE "public"."positioning" (
  "id" int8 NOT NULL DEFAULT nextval('positioning_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "indikator" varchar(255) COLLATE "pg_catalog"."default",
  "deskripsi" text COLLATE "pg_catalog"."default",
  "platform" text COLLATE "pg_catalog"."default",
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of positioning
-- ----------------------------
INSERT INTO "public"."positioning" VALUES (1, 1, 'Masalah yang dihadapi market.', '1.Belum banyak tempat nongkrong yang nyaman untuk duduk lama 2.Banyak tempat fokus ke kopi saja atau makan saja, positioning nya tidak jelas untuk nongkrong rame rame.', 'Instagram', '2026-01-19 07:51:10', '2026-01-19 07:51:10');
INSERT INTO "public"."positioning" VALUES (2, 1, 'Kompetitor.', 'Kafe nongkrong Bandung, coffee shop, dan restoran casual yang mendukung durasi nongkrong lama.', 'Instagram', '2026-01-19 07:59:48', '2026-01-19 08:01:59');
INSERT INTO "public"."positioning" VALUES (3, 1, 'Unique Selling Proposition (USP).', 'Tempat nongkrong yang menggabungkan makan enak, menu aman untuk semua selera, dan suasana yang mendukung ngobrol lama.', 'Instagram', '2026-01-19 08:00:22', '2026-01-19 08:02:09');
INSERT INTO "public"."positioning" VALUES (4, 1, 'Keuntungan konten terhadap pesaing.', 'Konten menampilkan momen kumpul bareng dan meja makan rame yang jarang ditunjukkan kompetitor.', 'Instagram', '2026-01-19 08:01:03', '2026-01-19 08:02:17');
INSERT INTO "public"."positioning" VALUES (5, 1, 'Solusi.', 'Menunjukkan bahwa nongkrong lama sambil makan enak bisa dilakukan tanpa ribet di Reunion House.', 'Instagram', '2026-01-19 08:01:28', '2026-01-19 08:02:25');

-- ----------------------------
-- Table structure for produk_client
-- ----------------------------
DROP TABLE IF EXISTS "public"."produk_client";
CREATE TABLE "public"."produk_client" (
  "id" int8 NOT NULL DEFAULT nextval('produk_client_id_seq'::regclass),
  "kategori_jasa_id" int8 NOT NULL,
  "nama" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "status" int4 NOT NULL,
  "access_token" json,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of produk_client
-- ----------------------------
INSERT INTO "public"."produk_client" VALUES (1, 1, 'Reunion House', 1, '{"platform":["Instagram"],"access":[null]}', '2026-01-19 06:06:10', '2026-01-19 06:06:10');
INSERT INTO "public"."produk_client" VALUES (2, 1, 'Gafi', 1, '{"platform":["Instagram"],"access":[null]}', '2026-01-19 06:09:16', '2026-01-19 06:09:16');

-- ----------------------------
-- Table structure for produk_jasa
-- ----------------------------
DROP TABLE IF EXISTS "public"."produk_jasa";
CREATE TABLE "public"."produk_jasa" (
  "id" int8 NOT NULL DEFAULT nextval('produk_jasa_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "jasa" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of produk_jasa
-- ----------------------------

-- ----------------------------
-- Table structure for segmentasi
-- ----------------------------
DROP TABLE IF EXISTS "public"."segmentasi";
CREATE TABLE "public"."segmentasi" (
  "id" int8 NOT NULL DEFAULT nextval('segmentasi_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "usia" varchar(255) COLLATE "pg_catalog"."default",
  "gender" varchar(255) COLLATE "pg_catalog"."default",
  "negara" varchar(255) COLLATE "pg_catalog"."default",
  "wilayah" varchar(255) COLLATE "pg_catalog"."default",
  "gaya_hidup" varchar(255) COLLATE "pg_catalog"."default",
  "status_sosial" varchar(255) COLLATE "pg_catalog"."default",
  "minat" varchar(255) COLLATE "pg_catalog"."default",
  "penggunaan" varchar(255) COLLATE "pg_catalog"."default",
  "loyalitas" varchar(255) COLLATE "pg_catalog"."default",
  "manfaat" varchar(255) COLLATE "pg_catalog"."default",
  "platform" varchar(255) COLLATE "pg_catalog"."default",
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of segmentasi
-- ----------------------------
INSERT INTO "public"."segmentasi" VALUES (3, 1, '35-45 tahun keatas', 'Semua', 'Indonesia.', 'Bandung&Jakarta.', '1.Berasama keluarga 2.Sosial 3.Makan bareng.', 'Pekerja mapan/Pengusaha.', '1.Tempat makan nyaman dan aman untuk semua usia 2.Terbiasa mengeluarkan uang untuk gaya hidup/hobi.', 'Respon ke konten yang terlihat rapi dan tidak berisik.', 'Tinggi jika pengalaman nyaman.', 'Tempat kumpul keluarga dan acara santai.', 'Instagram', '2026-01-19 07:28:46', '2026-01-19 07:29:07');
INSERT INTO "public"."segmentasi" VALUES (1, 1, '20-25 Tahun.', 'Semua', 'Indonesia.', 'Bandung.', '1.Aktif 2.Sering nongkrong 3.Suka kumpul rame rame.', 'Pelajar dan pekerja awal.', '1.Nongkrong 2.Makan enak 3.Tempat nyaman.', 'Tertarik konten harga menu dan porsi.', '1.Sedang 2.Datang saat ada ajakan teman.', 'Tempat nongkrong lama dengan harga masuk akal.', 'Instagram', '2026-01-19 07:06:50', '2026-01-19 07:30:04');
INSERT INTO "public"."segmentasi" VALUES (2, 1, '25 - 35 tahun', 'Semua', 'Indonesia.', 'Bandung.', '1.Sosial 2.Work life balance 3.Suka kumpul santai.', 'Pekerja tetap dan freelance.', '1.Nongkrong setelah kerja 2.Gathering kecil.', 'Perhatikan visual meja makan dan ambience.', 'Loyalitas Cukup tinggi jika cocok.', 'Manfaat Tempat nyaman untuk ngobrol lama dan makan bareng.', 'Instagram', '2026-01-19 07:15:53', '2026-01-19 07:31:25');

-- ----------------------------
-- Table structure for sessions
-- ----------------------------
DROP TABLE IF EXISTS "public"."sessions";
CREATE TABLE "public"."sessions" (
  "id" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "user_id" int8,
  "ip_address" varchar(45) COLLATE "pg_catalog"."default",
  "user_agent" text COLLATE "pg_catalog"."default",
  "payload" text COLLATE "pg_catalog"."default" NOT NULL,
  "last_activity" int4 NOT NULL
)
;

-- ----------------------------
-- Records of sessions
-- ----------------------------
INSERT INTO "public"."sessions" VALUES ('J6cI5fboNqYMA7T7B09zuUD4DqbsCIAzs0j8c4GZ', 1, '192.168.0.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWmNucWhOamFsUzdOWkoxSUd6T1hDWnczY1BNSWk4b096UHgwSVVKayI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTM6Imh0dHA6Ly8xOTIuMTY4LjAuMTAzOjgwMDAvbWFya2V0X3Jlc2VhcmNoLzEvSW5zdGFncmFtIjtzOjU6InJvdXRlIjtzOjE1OiJtYXJrZXRfcmVzZWFyY2giO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1768811782);
INSERT INTO "public"."sessions" VALUES ('7GKtvglefAIU9Xz4UJJMtBWrQ6YPjimrB0ReqWAz', 1, '192.168.0.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiTTRKdWdEblJ2anU2dXQxbzVQNFhNZnh5WnN3dTl2NUVNOWFRNWhlZyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MDoiaHR0cDovLzE5Mi4xNjguMC4xMDM6ODAwMC9tYW5hamVtZW5fdXNlciI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjUzOiJodHRwOi8vMTkyLjE2OC4wLjEwMzo4MDAwL21hcmtldF9yZXNlYXJjaC8xL0luc3RhZ3JhbSI7czo1OiJyb3V0ZSI7czoxNToibWFya2V0X3Jlc2VhcmNoIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1768811858);
INSERT INTO "public"."sessions" VALUES ('Ugfbqf8BXTTM3cSMiGmd2MFtY1APqySwtrJ3dXwg', 3, '192.168.0.103', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiQm9aMndFQXlDMnlrNXVQd2ZMOFFvQlZxcFhsdkZKc2lEM1JtdGFDMCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMDoiaHR0cDovLzE5Mi4xNjguMC4xMDM6ODAwMC9ob21lIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjU6Imh0dHA6Ly8xOTIuMTY4LjAuMTAzOjgwMDAiO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTozO30=', 1768798477);

-- ----------------------------
-- Table structure for status
-- ----------------------------
DROP TABLE IF EXISTS "public"."status";
CREATE TABLE "public"."status" (
  "id" int8 NOT NULL DEFAULT nextval('status_id_seq'::regclass),
  "nama_status" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "status" int4 NOT NULL,
  "kategori_id" int4,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of status
-- ----------------------------

-- ----------------------------
-- Table structure for strategy
-- ----------------------------
DROP TABLE IF EXISTS "public"."strategy";
CREATE TABLE "public"."strategy" (
  "id" int8 NOT NULL DEFAULT nextval('strategy_id_seq'::regclass),
  "strategy" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "status" int4 NOT NULL,
  "kategori_id" int4,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of strategy
-- ----------------------------

-- ----------------------------
-- Table structure for target_audience
-- ----------------------------
DROP TABLE IF EXISTS "public"."target_audience";
CREATE TABLE "public"."target_audience" (
  "id" int8 NOT NULL DEFAULT nextval('target_audience_id_seq'::regclass),
  "produk_client_id" int8 NOT NULL,
  "indikator" varchar(255) COLLATE "pg_catalog"."default",
  "usia" varchar(255) COLLATE "pg_catalog"."default",
  "gender" varchar(255) COLLATE "pg_catalog"."default",
  "negara" varchar(255) COLLATE "pg_catalog"."default",
  "wilayah" varchar(255) COLLATE "pg_catalog"."default",
  "pekerjaan" varchar(255) COLLATE "pg_catalog"."default",
  "gaya_hidup" varchar(255) COLLATE "pg_catalog"."default",
  "status_sosial" varchar(255) COLLATE "pg_catalog"."default",
  "penggunaan" varchar(255) COLLATE "pg_catalog"."default",
  "manfaat" varchar(255) COLLATE "pg_catalog"."default",
  "platform" varchar(255) COLLATE "pg_catalog"."default",
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of target_audience
-- ----------------------------
INSERT INTO "public"."target_audience" VALUES (2, 1, NULL, '1323', 'Laki-laki', '2313', '2131', '23131', '312321', '41241', '2313', '213131', 'Instagram', '2026-01-19 07:42:24', '2026-01-19 08:17:02');

-- ----------------------------
-- Table structure for type_produk
-- ----------------------------
DROP TABLE IF EXISTS "public"."type_produk";
CREATE TABLE "public"."type_produk" (
  "id" int8 NOT NULL DEFAULT nextval('type_produk_id_seq'::regclass),
  "type" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "status" int4 NOT NULL,
  "kategori_id" int4,
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of type_produk
-- ----------------------------

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS "public"."users";
CREATE TABLE "public"."users" (
  "id" int8 NOT NULL DEFAULT nextval('users_id_seq'::regclass),
  "name" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "email" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "email_verified_at" timestamp(0),
  "password" varchar(255) COLLATE "pg_catalog"."default" NOT NULL,
  "role" varchar(255) COLLATE "pg_catalog"."default",
  "hod" int8,
  "dmm" int8,
  "dm" int8,
  "cc" int8,
  "divisi" json,
  "client" json,
  "remember_token" varchar(100) COLLATE "pg_catalog"."default",
  "created_at" timestamp(0),
  "updated_at" timestamp(0)
)
;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO "public"."users" VALUES (9, 'Ramma', 'ramma.m.f@lightermediagroup.com', NULL, '$2y$12$IZjPQj9UeR0YAvaMqNFhUOAjXS5NSeTj63DskOGS1o7Oo17J0xp1K', 'head_of_division', NULL, NULL, NULL, NULL, '[1]', '[]', NULL, '2026-01-19 04:33:26', '2026-01-19 04:35:53');
INSERT INTO "public"."users" VALUES (2, 'Yulis', 'yulis@lightermediagroup.com', NULL, '$2y$12$N1c1HN2SeudpoJSVGcMJ2OqbCaproow1PIaXhrUxqoTwCT59ggqn.', 'digital_marketing', 9, NULL, NULL, NULL, '[1,2]', '[]', NULL, '2026-01-19 04:09:04', '2026-01-19 05:10:47');
INSERT INTO "public"."users" VALUES (8, 'Aditya', 'aditya07@lightermediagroup.com', NULL, '$2y$12$9zmjPhZILcS.t9Qdk0jcD.iCz9TH1nJ9Gkbe/dioSDsSrMMc/Echu', 'content_creator', 9, NULL, 2, NULL, '[2]', '[]', NULL, '2026-01-19 04:32:34', '2026-01-19 05:10:47');
INSERT INTO "public"."users" VALUES (1, 'admin', 'admin@gmail.com', NULL, '$2y$12$rgA50seuQnuzYs4/PqkPdOmvq/F9yPj6gMo4GIx6ee4c6eK4cNUES', 'super_admin', NULL, NULL, NULL, NULL, '[]', '[]', 'vsvydDMnqckD0lf4pveKHyRFxn7eYHEK4Kz3krxAh7l69FRwLqFMJf6KRoPt', '2026-01-19 03:47:49', '2026-01-19 03:47:49');

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."alat_branding_id_seq"
OWNED BY "public"."alat_branding"."id";
SELECT setval('"public"."alat_branding_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."analize_4p_id_seq"
OWNED BY "public"."analize_4p"."id";
SELECT setval('"public"."analize_4p_id_seq"', 15, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."analize_swot_id_seq"
OWNED BY "public"."analize_swot"."id";
SELECT setval('"public"."analize_swot_id_seq"', 5, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."audio_brand_id_seq"
OWNED BY "public"."audio_brand"."id";
SELECT setval('"public"."audio_brand_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."brand_identify_id_seq"
OWNED BY "public"."brand_identify"."id";
SELECT setval('"public"."brand_identify_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."brand_image_id_seq"
OWNED BY "public"."brand_image"."id";
SELECT setval('"public"."brand_image_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."brand_tagline_id_seq"
OWNED BY "public"."brand_tagline"."id";
SELECT setval('"public"."brand_tagline_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."brand_value_id_seq"
OWNED BY "public"."brand_value"."id";
SELECT setval('"public"."brand_value_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."data_jasa_id_seq"
OWNED BY "public"."data_jasa"."id";
SELECT setval('"public"."data_jasa_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."failed_jobs_id_seq"
OWNED BY "public"."failed_jobs"."id";
SELECT setval('"public"."failed_jobs_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."gaya_komunikasi_id_seq"
OWNED BY "public"."gaya_komunikasi"."id";
SELECT setval('"public"."gaya_komunikasi_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."hooks_id_seq"
OWNED BY "public"."hooks"."id";
SELECT setval('"public"."hooks_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."jenis_body_id_seq"
OWNED BY "public"."jenis_body"."id";
SELECT setval('"public"."jenis_body_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."jenis_cta_id_seq"
OWNED BY "public"."jenis_cta"."id";
SELECT setval('"public"."jenis_cta_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."jobs_id_seq"
OWNED BY "public"."jobs"."id";
SELECT setval('"public"."jobs_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."kategori_jasa_id_seq"
OWNED BY "public"."kategori_jasa"."id";
SELECT setval('"public"."kategori_jasa_id_seq"', 3, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."komposisi_id_seq"
OWNED BY "public"."komposisi"."id";
SELECT setval('"public"."komposisi_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."migrations_id_seq"
OWNED BY "public"."migrations"."id";
SELECT setval('"public"."migrations_id_seq"', 29, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."moodboard_id_seq"
OWNED BY "public"."moodboard"."id";
SELECT setval('"public"."moodboard_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."personal_access_tokens_id_seq"
OWNED BY "public"."personal_access_tokens"."id";
SELECT setval('"public"."personal_access_tokens_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."pilar_id_seq"
OWNED BY "public"."pilar"."id";
SELECT setval('"public"."pilar_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."positioning_id_seq"
OWNED BY "public"."positioning"."id";
SELECT setval('"public"."positioning_id_seq"', 5, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."produk_client_id_seq"
OWNED BY "public"."produk_client"."id";
SELECT setval('"public"."produk_client_id_seq"', 3, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."produk_jasa_id_seq"
OWNED BY "public"."produk_jasa"."id";
SELECT setval('"public"."produk_jasa_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."segmentasi_id_seq"
OWNED BY "public"."segmentasi"."id";
SELECT setval('"public"."segmentasi_id_seq"', 3, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."status_id_seq"
OWNED BY "public"."status"."id";
SELECT setval('"public"."status_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."strategy_id_seq"
OWNED BY "public"."strategy"."id";
SELECT setval('"public"."strategy_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."target_audience_id_seq"
OWNED BY "public"."target_audience"."id";
SELECT setval('"public"."target_audience_id_seq"', 2, true);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."type_produk_id_seq"
OWNED BY "public"."type_produk"."id";
SELECT setval('"public"."type_produk_id_seq"', 1, false);

-- ----------------------------
-- Alter sequences owned by
-- ----------------------------
ALTER SEQUENCE "public"."users_id_seq"
OWNED BY "public"."users"."id";
SELECT setval('"public"."users_id_seq"', 11, true);

-- ----------------------------
-- Primary Key structure for table alat_branding
-- ----------------------------
ALTER TABLE "public"."alat_branding" ADD CONSTRAINT "alat_branding_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table analize_4p
-- ----------------------------
ALTER TABLE "public"."analize_4p" ADD CONSTRAINT "analize_4p_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table analize_swot
-- ----------------------------
ALTER TABLE "public"."analize_swot" ADD CONSTRAINT "analize_swot_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table audio_brand
-- ----------------------------
ALTER TABLE "public"."audio_brand" ADD CONSTRAINT "audio_brand_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table brand_identify
-- ----------------------------
ALTER TABLE "public"."brand_identify" ADD CONSTRAINT "brand_identify_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table brand_image
-- ----------------------------
ALTER TABLE "public"."brand_image" ADD CONSTRAINT "brand_image_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table brand_tagline
-- ----------------------------
ALTER TABLE "public"."brand_tagline" ADD CONSTRAINT "brand_tagline_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table brand_value
-- ----------------------------
ALTER TABLE "public"."brand_value" ADD CONSTRAINT "brand_value_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table cache
-- ----------------------------
ALTER TABLE "public"."cache" ADD CONSTRAINT "cache_pkey" PRIMARY KEY ("key");

-- ----------------------------
-- Primary Key structure for table cache_locks
-- ----------------------------
ALTER TABLE "public"."cache_locks" ADD CONSTRAINT "cache_locks_pkey" PRIMARY KEY ("key");

-- ----------------------------
-- Primary Key structure for table data_jasa
-- ----------------------------
ALTER TABLE "public"."data_jasa" ADD CONSTRAINT "data_jasa_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Uniques structure for table failed_jobs
-- ----------------------------
ALTER TABLE "public"."failed_jobs" ADD CONSTRAINT "failed_jobs_uuid_unique" UNIQUE ("uuid");

-- ----------------------------
-- Primary Key structure for table failed_jobs
-- ----------------------------
ALTER TABLE "public"."failed_jobs" ADD CONSTRAINT "failed_jobs_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table gaya_komunikasi
-- ----------------------------
ALTER TABLE "public"."gaya_komunikasi" ADD CONSTRAINT "gaya_komunikasi_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table hooks
-- ----------------------------
ALTER TABLE "public"."hooks" ADD CONSTRAINT "hooks_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table jenis_body
-- ----------------------------
ALTER TABLE "public"."jenis_body" ADD CONSTRAINT "jenis_body_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table jenis_cta
-- ----------------------------
ALTER TABLE "public"."jenis_cta" ADD CONSTRAINT "jenis_cta_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table job_batches
-- ----------------------------
ALTER TABLE "public"."job_batches" ADD CONSTRAINT "job_batches_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table jobs
-- ----------------------------
CREATE INDEX "jobs_queue_index" ON "public"."jobs" USING btree (
  "queue" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table jobs
-- ----------------------------
ALTER TABLE "public"."jobs" ADD CONSTRAINT "jobs_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table kategori_jasa
-- ----------------------------
ALTER TABLE "public"."kategori_jasa" ADD CONSTRAINT "kategori_jasa_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table komposisi
-- ----------------------------
ALTER TABLE "public"."komposisi" ADD CONSTRAINT "komposisi_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table migrations
-- ----------------------------
ALTER TABLE "public"."migrations" ADD CONSTRAINT "migrations_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table moodboard
-- ----------------------------
ALTER TABLE "public"."moodboard" ADD CONSTRAINT "moodboard_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table password_reset_tokens
-- ----------------------------
ALTER TABLE "public"."password_reset_tokens" ADD CONSTRAINT "password_reset_tokens_pkey" PRIMARY KEY ("email");

-- ----------------------------
-- Indexes structure for table personal_access_tokens
-- ----------------------------
CREATE INDEX "personal_access_tokens_expires_at_index" ON "public"."personal_access_tokens" USING btree (
  "expires_at" "pg_catalog"."timestamp_ops" ASC NULLS LAST
);
CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" ON "public"."personal_access_tokens" USING btree (
  "tokenable_type" COLLATE "pg_catalog"."default" "pg_catalog"."text_ops" ASC NULLS LAST,
  "tokenable_id" "pg_catalog"."int8_ops" ASC NULLS LAST
);

-- ----------------------------
-- Uniques structure for table personal_access_tokens
-- ----------------------------
ALTER TABLE "public"."personal_access_tokens" ADD CONSTRAINT "personal_access_tokens_token_unique" UNIQUE ("token");

-- ----------------------------
-- Primary Key structure for table personal_access_tokens
-- ----------------------------
ALTER TABLE "public"."personal_access_tokens" ADD CONSTRAINT "personal_access_tokens_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table pilar
-- ----------------------------
ALTER TABLE "public"."pilar" ADD CONSTRAINT "pilar_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table positioning
-- ----------------------------
ALTER TABLE "public"."positioning" ADD CONSTRAINT "positioning_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table produk_client
-- ----------------------------
ALTER TABLE "public"."produk_client" ADD CONSTRAINT "produk_client_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table produk_jasa
-- ----------------------------
ALTER TABLE "public"."produk_jasa" ADD CONSTRAINT "produk_jasa_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table segmentasi
-- ----------------------------
ALTER TABLE "public"."segmentasi" ADD CONSTRAINT "segmentasi_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Indexes structure for table sessions
-- ----------------------------
CREATE INDEX "sessions_last_activity_index" ON "public"."sessions" USING btree (
  "last_activity" "pg_catalog"."int4_ops" ASC NULLS LAST
);
CREATE INDEX "sessions_user_id_index" ON "public"."sessions" USING btree (
  "user_id" "pg_catalog"."int8_ops" ASC NULLS LAST
);

-- ----------------------------
-- Primary Key structure for table sessions
-- ----------------------------
ALTER TABLE "public"."sessions" ADD CONSTRAINT "sessions_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table status
-- ----------------------------
ALTER TABLE "public"."status" ADD CONSTRAINT "status_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table strategy
-- ----------------------------
ALTER TABLE "public"."strategy" ADD CONSTRAINT "strategy_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table target_audience
-- ----------------------------
ALTER TABLE "public"."target_audience" ADD CONSTRAINT "target_audience_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Primary Key structure for table type_produk
-- ----------------------------
ALTER TABLE "public"."type_produk" ADD CONSTRAINT "type_produk_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Uniques structure for table users
-- ----------------------------
ALTER TABLE "public"."users" ADD CONSTRAINT "users_email_unique" UNIQUE ("email");

-- ----------------------------
-- Primary Key structure for table users
-- ----------------------------
ALTER TABLE "public"."users" ADD CONSTRAINT "users_pkey" PRIMARY KEY ("id");

-- ----------------------------
-- Foreign Keys structure for table alat_branding
-- ----------------------------
ALTER TABLE "public"."alat_branding" ADD CONSTRAINT "alat_branding_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table analize_4p
-- ----------------------------
ALTER TABLE "public"."analize_4p" ADD CONSTRAINT "analize_4p_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table analize_swot
-- ----------------------------
ALTER TABLE "public"."analize_swot" ADD CONSTRAINT "analize_swot_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table audio_brand
-- ----------------------------
ALTER TABLE "public"."audio_brand" ADD CONSTRAINT "audio_brand_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table brand_identify
-- ----------------------------
ALTER TABLE "public"."brand_identify" ADD CONSTRAINT "brand_identify_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table brand_image
-- ----------------------------
ALTER TABLE "public"."brand_image" ADD CONSTRAINT "brand_image_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table brand_tagline
-- ----------------------------
ALTER TABLE "public"."brand_tagline" ADD CONSTRAINT "brand_tagline_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table brand_value
-- ----------------------------
ALTER TABLE "public"."brand_value" ADD CONSTRAINT "brand_value_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table data_jasa
-- ----------------------------
ALTER TABLE "public"."data_jasa" ADD CONSTRAINT "data_jasa_body_id_foreign" FOREIGN KEY ("body_id") REFERENCES "public"."jenis_body" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE "public"."data_jasa" ADD CONSTRAINT "data_jasa_cta_id_foreign" FOREIGN KEY ("cta_id") REFERENCES "public"."jenis_cta" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE "public"."data_jasa" ADD CONSTRAINT "data_jasa_hooks_id_foreign" FOREIGN KEY ("hooks_id") REFERENCES "public"."hooks" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE "public"."data_jasa" ADD CONSTRAINT "data_jasa_pilar_id_foreign" FOREIGN KEY ("pilar_id") REFERENCES "public"."pilar" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE "public"."data_jasa" ADD CONSTRAINT "data_jasa_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE "public"."data_jasa" ADD CONSTRAINT "data_jasa_strategy_id_foreign" FOREIGN KEY ("strategy_id") REFERENCES "public"."strategy" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;
ALTER TABLE "public"."data_jasa" ADD CONSTRAINT "data_jasa_type_produk_id_foreign" FOREIGN KEY ("type_produk_id") REFERENCES "public"."type_produk" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table gaya_komunikasi
-- ----------------------------
ALTER TABLE "public"."gaya_komunikasi" ADD CONSTRAINT "gaya_komunikasi_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table komposisi
-- ----------------------------
ALTER TABLE "public"."komposisi" ADD CONSTRAINT "komposisi_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table moodboard
-- ----------------------------
ALTER TABLE "public"."moodboard" ADD CONSTRAINT "moodboard_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table positioning
-- ----------------------------
ALTER TABLE "public"."positioning" ADD CONSTRAINT "positioning_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table produk_client
-- ----------------------------
ALTER TABLE "public"."produk_client" ADD CONSTRAINT "produk_client_kategori_jasa_id_foreign" FOREIGN KEY ("kategori_jasa_id") REFERENCES "public"."kategori_jasa" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table produk_jasa
-- ----------------------------
ALTER TABLE "public"."produk_jasa" ADD CONSTRAINT "produk_jasa_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table segmentasi
-- ----------------------------
ALTER TABLE "public"."segmentasi" ADD CONSTRAINT "segmentasi_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table target_audience
-- ----------------------------
ALTER TABLE "public"."target_audience" ADD CONSTRAINT "target_audience_produk_client_id_foreign" FOREIGN KEY ("produk_client_id") REFERENCES "public"."produk_client" ("id") ON DELETE CASCADE ON UPDATE NO ACTION;

-- ----------------------------
-- Foreign Keys structure for table users
-- ----------------------------
ALTER TABLE "public"."users" ADD CONSTRAINT "users_cc_foreign" FOREIGN KEY ("cc") REFERENCES "public"."users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
ALTER TABLE "public"."users" ADD CONSTRAINT "users_dm_foreign" FOREIGN KEY ("dm") REFERENCES "public"."users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
ALTER TABLE "public"."users" ADD CONSTRAINT "users_dmm_foreign" FOREIGN KEY ("dmm") REFERENCES "public"."users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
ALTER TABLE "public"."users" ADD CONSTRAINT "users_hod_foreign" FOREIGN KEY ("hod") REFERENCES "public"."users" ("id") ON DELETE SET NULL ON UPDATE NO ACTION;
