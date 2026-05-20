-- Auto-generated DDL from migration-built SQLite schema
-- Generated at (UTC): 2026-02-25T06:54:37.855903+00:00

-- Table: api_client_logs
CREATE TABLE "api_client_logs" ("id" integer primary key autoincrement not null, "method" varchar not null, "endpoint" varchar not null, "request" text not null, "response" text not null, "user_agent" varchar, "status" varchar not null default 'NEW', "created_at" datetime, "updated_at" datetime);

-- Table: audios
CREATE TABLE "audios" ("id" integer primary key autoincrement not null, "company_id" integer, "code" varchar not null, "text" varchar not null, "voice" varchar not null default 'alloy', "file_path" varchar, "link" varchar, "created_by" integer, "updated_by" integer, "created_at" datetime, "updated_at" datetime, foreign key("company_id") references "companys"("id"), foreign key("created_by") references "users"("id"), foreign key("updated_by") references "users"("id"));
CREATE INDEX "audios_code_index" on "audios" ("code");
CREATE UNIQUE INDEX "audios_code_unique" on "audios" ("code");
CREATE INDEX "audios_company_id_index" on "audios" ("company_id");
CREATE INDEX "audios_created_by_index" on "audios" ("created_by");
CREATE INDEX "audios_updated_by_index" on "audios" ("updated_by");

-- Table: campaign_attachments
CREATE TABLE "campaign_attachments" ("id" integer primary key autoincrement not null, "event_file_id" integer, "name" varchar not null, "file_path" varchar not null, "mime" varchar not null, "created_by" integer, "updated_by" integer, "created_at" datetime, "updated_at" datetime, foreign key("event_file_id") references "event_files"("id"), foreign key("created_by") references "users"("id"), foreign key("updated_by") references "users"("id"));
CREATE INDEX "campaign_attachments_created_by_index" on "campaign_attachments" ("created_by");
CREATE INDEX "campaign_attachments_event_file_id_index" on "campaign_attachments" ("event_file_id");
CREATE INDEX "campaign_attachments_updated_by_index" on "campaign_attachments" ("updated_by");

-- Table: campaign_details
CREATE TABLE "campaign_details" ("id" integer primary key autoincrement not null, "campaign_id" integer not null, "tag_id" integer, "name" varchar not null, "qrcode" varchar not null, "gender" varchar, "email" varchar, "phone" varchar, "send_email" tinyint(1) not null default '0', "send_zalo" tinyint(1) not null default '0', "send_sms" tinyint(1) not null default '0', "status" varchar, "created_at" datetime, "updated_at" datetime, "img_qrcode" varchar, "custom_fields" text, "email_form" tinyint(1) default '1', "document_pdf" varchar, foreign key("campaign_id") references "campaigns"("id"), foreign key("tag_id") references "tags"("id"));
CREATE INDEX "campaign_details_campaign_id_index" on "campaign_details" ("campaign_id");
CREATE INDEX "campaign_details_tag_id_index" on "campaign_details" ("tag_id");

-- Table: campaigns
CREATE TABLE "campaigns" ("id" integer primary key autoincrement not null, "event_id" integer not null, "name" varchar not null, "status" varchar, "created_at" datetime, "updated_at" datetime, "type" varchar, "template_id" varchar not null default ('email-template-default'), "total_emails" numeric, "subject" varchar, "from_email" varchar, "from_name" varchar, "cc" text, "bcc" text, "fixed_attachments" tinyint(1) not null default '1', "created_by" integer, "updated_by" integer, "limitation_per_time" integer default '15', "hold_time" integer default '10', "is_online" tinyint(1) default '0', "message_stream" varchar not null default 'outbound', foreign key("event_id") references events("id") on delete no action on update no action, foreign key("created_by") references "users"("id"), foreign key("updated_by") references "users"("id"));
CREATE INDEX "campaigns_created_by_index" on "campaigns" ("created_by");
CREATE INDEX "campaigns_event_id_index" on "campaigns" ("event_id");
CREATE INDEX "campaigns_updated_by_index" on "campaigns" ("updated_by");

-- Table: card_details
CREATE TABLE "card_details" ("id" integer primary key autoincrement not null, "card_id" integer not null, "card_code" varchar not null, "type" varchar not null, "field" varchar, "text" varchar default 'TEXT', "text_wrap" integer not null default '0', "img_path" varchar, "pos_x" numeric not null default '10', "pos_y" numeric not null default '10', "size" numeric default '300', "font_size" numeric default '50', "font" varchar default 'svn-arial/SVN-Arial-Bold.ttf', "width" numeric default '300', "height" numeric default '300', "bold" tinyint(1) not null default '0', "italic" tinyint(1) not null default '0', "color" varchar default '#000000', "v_align" varchar not null default 'top', "h_align" varchar not null default 'left', "rotate" varchar not null default '0', "status" varchar not null default 'ACTIVE', "created_at" datetime, "updated_at" datetime, foreign key("card_id") references "cards"("id"));
CREATE INDEX "card_details_card_id_index" on "card_details" ("card_id");

-- Table: cards
CREATE TABLE "cards" ("id" integer primary key autoincrement not null, "event_id" integer not null, "event_code" varchar not null, "code" varchar not null, "client_type" varchar, "file_name_template" varchar, "background" varchar, "extension" varchar default 'png', "scaled" integer, "type" varchar, "note" varchar, "status" varchar not null default 'ACTIVE', "created_at" datetime, "updated_at" datetime, "device" varchar not null default 'BOTH');

-- Table: checkins
CREATE TABLE "checkins" ("id" integer primary key autoincrement not null, "event_id" integer not null, "user_id" integer, "qrcode" varchar not null, "client_name" varchar, "source" varchar, "scan_time" datetime not null, "note" text, "status" varchar not null default 'NEW', "created_at" datetime, "updated_at" datetime, "custom_fields" text, "event_code" varchar, "type" varchar not null default 'CHECKIN', foreign key("event_id") references "events"("id") on delete cascade, foreign key("user_id") references "users"("id") on delete cascade);
CREATE INDEX "checkins_event_id_index" on "checkins" ("event_id");
CREATE INDEX "checkins_user_id_index" on "checkins" ("user_id");

-- Table: client_backups
CREATE TABLE "client_backups" ("id" integer primary key autoincrement not null, "batch_key" varchar not null, "event_id" integer not null, "country_id" integer, "org_id" integer not null, "event_code" varchar not null, "qrcode" varchar, "name" varchar not null, "email" varchar, "type" varchar, "register_source" varchar, "custom_fields" text, "created_at" datetime, "updated_at" datetime);

-- Table: client_tickets
CREATE TABLE "client_tickets" ("id" integer primary key autoincrement not null, "event_id" integer, "client_id" integer, "ticket_id" integer not null, "is_link" tinyint(1) not null default ('0'), "img_path" varchar, "created_at" datetime, "updated_at" datetime, "order_id" integer, foreign key("ticket_id") references tickets("id") on delete cascade on update cascade, foreign key("client_id") references clients("id") on delete set null on update cascade, foreign key("event_id") references events("id") on delete no action on update no action, foreign key("order_id") references "orders"("id"));
CREATE INDEX "client_tickets_client_id_index" on "client_tickets" ("client_id");
CREATE INDEX "client_tickets_event_id_index" on "client_tickets" ("event_id");
CREATE INDEX "client_tickets_order_id_index" on "client_tickets" ("order_id");
CREATE INDEX "client_tickets_ticket_id_index" on "client_tickets" ("ticket_id");

-- Table: clients
CREATE TABLE "clients" ("id" integer primary key autoincrement not null, "event_id" integer not null, "event_code" varchar, "ref_id" integer, "lp_id" integer, "qrcode" varchar not null, "name" varchar not null, "email" varchar, "custom_fields" text, "status" varchar not null default ('ACTIVE'), "created_by" integer, "updated_by" integer, "created_at" datetime, "updated_at" datetime, "img_qrcode" varchar, "avatar" varchar, "type" varchar default ('NORMAL'), "document_pdf" varchar, "register_source" varchar, "country_id" integer, "lang" varchar, "card_link_mobile" varchar, "card_link_desktop" varchar, foreign key("updated_by") references users("id") on delete no action on update no action, foreign key("created_by") references users("id") on delete no action on update no action, foreign key("event_id") references events("id") on delete no action on update no action, foreign key("country_id") references "countrys"("id"));
CREATE INDEX "clients_country_id_index" on "clients" ("country_id");
CREATE INDEX "clients_email_index" on "clients" ("email");
CREATE INDEX "clients_event_code_index" on "clients" ("event_code");
CREATE INDEX "clients_event_id_index" on "clients" ("event_id");
CREATE UNIQUE INDEX "clients_event_id_qrcode_unique" on "clients" ("event_id", "qrcode");
CREATE INDEX "clients_name_index" on "clients" ("name");
CREATE INDEX "clients_qrcode_index" on "clients" ("qrcode");

-- Table: comments
CREATE TABLE "comments" ("id" integer primary key autoincrement not null, "author_id" integer not null default '0', "post_id" integer not null, "content" text not null, "posted_at" datetime not null, "created_at" datetime, "updated_at" datetime, foreign key("author_id") references "users"("id") on delete cascade, foreign key("post_id") references "posts"("id") on delete cascade);

-- Table: companys
CREATE TABLE "companys" ("id" integer primary key autoincrement not null, "name" varchar not null, "status" varchar not null default ('ACTIVE'), "created_at" datetime, "updated_at" datetime, "is_default" tinyint(1) not null default ('0'), "limited_events" integer, "limited_clients" integer, "limited_emails" integer, "limited_users" integer, "limited_campaigns" integer, "created_by" integer, "updated_by" integer, "code" varchar not null, "license" varchar, "languages" text, "settings" text, "devices" text, "templates" text, "senders" text, "type" varchar, foreign key("updated_by") references users("id") on delete no action on update no action, foreign key("created_by") references users("id") on delete no action on update no action);
CREATE UNIQUE INDEX "companys_code_unique" on "companys" ("code");

-- Table: countrys
CREATE TABLE "countrys" ("id" integer primary key autoincrement not null, "code" varchar not null, "name" varchar not null, "is_default" tinyint(1) not null default '0', "description" varchar, "link_flag" varchar, "alt" varchar, "status" varchar not null default 'ACTIVE', "created_at" datetime, "updated_at" datetime);

-- Table: custom_field_templates
CREATE TABLE "custom_field_templates" ("id" integer primary key autoincrement not null, "event_id" integer not null, "is_default" tinyint(1) not null default '0', "is_show" tinyint(1) not null default '0', "is_lp" tinyint(1) not null default '0', "is_checkin_mobile" tinyint(1) not null default '0', "is_checkin_desktop" tinyint(1) not null default '0', "show_prefix" tinyint(1) not null default '0', "required" tinyint(1) not null default '0', "unique" tinyint(1) not null default '0', "name" varchar not null, "description" varchar, "placeholder" varchar, "icon" varchar, "order" integer not null, "type" varchar not null default 'TEXT', "accepts" text, "options" text, "checkins" text, "landing_page" text, "created_at" datetime, "updated_at" datetime, foreign key("event_id") references "events"("id"));
CREATE INDEX "custom_field_templates_event_id_index" on "custom_field_templates" ("event_id");
CREATE UNIQUE INDEX "custom_field_templates_event_id_name_unique" on "custom_field_templates" ("event_id", "name");

-- Table: email_templates
CREATE TABLE "email_templates" ("id" integer primary key autoincrement not null, "ref_id" varchar, "uuid" varchar, "name" varchar not null, "subject" varchar, "banner" varchar, "footer" varchar, "texts" text, "html" text, "status" varchar default 'ACTIVE', "created_at" datetime, "updated_at" datetime);
CREATE INDEX "email_templates_ref_id_index" on "email_templates" ("ref_id");
CREATE INDEX "email_templates_uuid_index" on "email_templates" ("uuid");
CREATE UNIQUE INDEX "email_templates_uuid_unique" on "email_templates" ("uuid");

-- Table: emails
CREATE TABLE "emails" ("id" integer primary key autoincrement not null, "campaign_id" integer not null, "subject" varchar, "email" varchar, "content" text, "sent_at" datetime, "from_name" varchar, "from_email" varchar, "to_name" varchar, "to_email" varchar, "status" varchar, "created_at" datetime, "updated_at" datetime, "param" text, "template_id" varchar, "supplier" varchar, "is_online" tinyint(1) default '0', "error_log" text, "qrcode" varchar, "message_id" varchar, "server_response" text, foreign key("campaign_id") references "campaigns"("id"));
CREATE INDEX "emails_campaign_id_index" on "emails" ("campaign_id");
CREATE INDEX "emails_qrcode_index" on "emails" ("qrcode");

-- Table: event_areas
CREATE TABLE "event_areas" ("id" integer primary key autoincrement not null, "event_id" integer not null, "name" varchar not null, "client_types" text, "description" text, "note" varchar, "created_at" datetime, "updated_at" datetime);

-- Table: event_file_logs
CREATE TABLE "event_file_logs" ("id" integer primary key autoincrement not null, "event_id" integer, "event_code" varchar, "name" varchar not null, "path" varchar not null, "type" varchar default 'FILE', "created_at" datetime, "updated_at" datetime);

-- Table: event_files
CREATE TABLE "event_files" ("id" integer primary key autoincrement not null, "event_id" integer not null, "media_id" integer, "name" varchar not null, "file_path" varchar not null, "is_public" tinyint(1) not null default '1', "type" varchar not null default 'FILE', "status" varchar not null default 'ACTIVE', "created_at" datetime, "updated_at" datetime, foreign key("event_id") references "events"("id"), foreign key("media_id") references "media"("id") on delete set null);
CREATE INDEX "event_files_event_id_index" on "event_files" ("event_id");
CREATE UNIQUE INDEX "event_files_file_path_unique" on "event_files" ("file_path");
CREATE INDEX "event_files_media_id_index" on "event_files" ("media_id");

-- Table: event_settings
CREATE TABLE "event_settings" ("id" integer primary key autoincrement not null, "parent_id" integer, "event_id" integer not null, "name" varchar not null, "description" varchar, "value" text, "options" text, "group" varchar, "input_type" varchar not null default 'text', "created_at" datetime, "updated_at" datetime, "status" varchar not null default 'ACTIVE', foreign key("event_id") references "events"("id"), foreign key("parent_id") references "event_settings"("id"));
CREATE INDEX "event_settings_event_id_index" on "event_settings" ("event_id");
CREATE UNIQUE INDEX "event_settings_event_id_name_group_unique" on "event_settings" ("event_id", "name", "group");
CREATE INDEX "event_settings_parent_id_index" on "event_settings" ("parent_id");

-- Table: event_types
CREATE TABLE "event_types" ("id" integer primary key autoincrement not null, "title" varchar not null, "name" varchar not null, "description" varchar, "created_at" datetime, "updated_at" datetime);

-- Table: events
CREATE TABLE "events" ("id" integer primary key autoincrement not null, "company_id" integer not null, "code" varchar not null, "name" varchar not null, "description" text, "place" varchar, "features" text, "languages" text not null default (JSON_ARRAY()), "main_bg_mobile" varchar, "contact_person" varchar, "contact_phone" varchar, "contact_email" varchar, "note" text, "status" varchar not null default ('ACTIVE'), "created_at" datetime, "updated_at" datetime, "more_images" text, "main_bg_desktop" varchar, "main_bglandingpage_desktop" varchar, "main_bglandingpage_mobile" varchar, "sound_success" varchar, "sound_fail" varchar, "custom_checkin_messages" text, "is_default" tinyint(1) not null default ('0'), "logo" varchar, "favicon" varchar, "from_date" date, "to_date" date, "created_by" integer, "updated_by" integer, "import_error_log" text, "province_id" integer, "ref_id" integer, "type_id" integer, "assignee_id" integer, foreign key("province_id") references provinces("id") on delete set null on update no action, foreign key("company_id") references companys("id") on delete no action on update no action, foreign key("created_by") references users("id") on delete no action on update no action, foreign key("updated_by") references users("id") on delete no action on update no action, foreign key("type_id") references "event_types"("id") on delete set null, foreign key("assignee_id") references "users"("id") on delete set null);
CREATE INDEX "events_assignee_id_index" on "events" ("assignee_id");
CREATE INDEX "events_code_index" on "events" ("code");
CREATE INDEX "events_company_id_index" on "events" ("company_id");
CREATE INDEX "events_province_id_index" on "events" ("province_id");
CREATE INDEX "events_type_id_index" on "events" ("type_id");

-- Table: export_datas
CREATE TABLE "export_datas" ("id" integer primary key autoincrement not null, "event_id" integer not null, "user_id" integer not null, "name" varchar, "file_path" varchar, "status" varchar not null default 'EXPORTED', "type" varchar default 'EXPORT_CLIENT', "created_at" datetime, "updated_at" datetime, "file_name" varchar, foreign key("event_id") references "events"("id"), foreign key("user_id") references "users"("id"));
CREATE INDEX "export_datas_event_id_index" on "export_datas" ("event_id");
CREATE INDEX "export_datas_user_id_index" on "export_datas" ("user_id");

-- Table: failed_jobs
CREATE TABLE "failed_jobs" ("id" integer primary key autoincrement not null, "uuid" varchar not null, "connection" text not null, "queue" text not null, "payload" text not null, "exception" text not null, "failed_at" datetime not null default CURRENT_TIMESTAMP);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs" ("uuid");

-- Table: historys
CREATE TABLE "historys" ("id" integer primary key autoincrement not null, "user_id" integer, "action" varchar not null, "object" varchar, "function" varchar, "method" varchar, "parameters" text, "error" varchar, "created_at" datetime, "updated_at" datetime);
CREATE INDEX "historys_user_id_index" on "historys" ("user_id");

-- Table: impexp_files
CREATE TABLE "impexp_files" ("id" integer primary key autoincrement not null, "event_id" integer, "name" varchar not null, "table" varchar not null, "file_path" varchar not null, "total_record_before" integer not null default '0', "total_record" integer not null default '0', "error_log" text, "type" varchar not null default 'IMPORT', "status" varchar not null default 'NEW', "created_at" datetime, "updated_at" datetime, foreign key("event_id") references "events"("id"));
CREATE INDEX "impexp_files_event_id_index" on "impexp_files" ("event_id");

-- Table: jobs
CREATE TABLE "jobs" ("id" integer primary key autoincrement not null, "queue" varchar not null, "payload" text not null, "attempts" integer not null, "reserved_at" integer, "available_at" integer not null, "created_at" integer not null);
CREATE INDEX "jobs_queue_reserved_at_index" on "jobs" ("queue", "reserved_at");

-- Table: label_details
CREATE TABLE "label_details" ("id" integer primary key autoincrement not null, "label_id" integer not null, "field" varchar not null, "type" varchar, "pos_x" numeric not null default '10', "pos_y" numeric not null default '10', "v_align" varchar not null default 'left', "h_align" varchar not null default 'top', "color" varchar not null default '#000000', "font" varchar, "size" numeric not null default '15', "unit" varchar not null default 'px', "width" varchar not null default '50', "status" varchar not null default 'ACTIVE', "created_at" datetime, "updated_at" datetime, "bold" tinyint(1) not null default '0', "italic" tinyint(1) not null default '0', "uppercase" tinyint(1) not null default '0', "value" varchar, foreign key("label_id") references "labels"("id"));
CREATE INDEX "label_details_label_id_index" on "label_details" ("label_id");

-- Table: labels
CREATE TABLE "labels" ("id" integer primary key autoincrement not null, "event_id" integer not null, "is_default" tinyint(1) not null default ('0'), "name" varchar not null, "width" numeric not null default ('1'), "height" numeric not null default ('1'), "unit" varchar not null default ('%'), "type" varchar default ('1'), "status" varchar not null default ('ACTIVE'), "created_at" datetime, "updated_at" datetime, "font" varchar, "font_link" varchar, "created_by" integer, "updated_by" integer, "rotate" integer not null default '0', foreign key("event_id") references events("id") on delete no action on update no action, foreign key("created_by") references "users"("id"), foreign key("updated_by") references "users"("id"));
CREATE INDEX "labels_event_id_index" on "labels" ("event_id");

-- Table: landing_page_campaigns
CREATE TABLE "landing_page_campaigns" ("id" integer primary key autoincrement not null, "landing_page_id" integer not null, "campaign_id" integer not null, "lang" varchar, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime);
CREATE INDEX "landing_page_campaigns_campaign_id_index" on "landing_page_campaigns" ("campaign_id");
CREATE INDEX "landing_page_campaigns_landing_page_id_index" on "landing_page_campaigns" ("landing_page_id");

-- Table: landing_page_cards
CREATE TABLE "landing_page_cards" ("id" integer primary key autoincrement not null, "landing_page_id" integer not null, "card_id" integer not null, "lang" varchar, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime);
CREATE INDEX "landing_page_cards_card_id_index" on "landing_page_cards" ("card_id");
CREATE INDEX "landing_page_cards_landing_page_id_index" on "landing_page_cards" ("landing_page_id");

-- Table: landing_pages
CREATE TABLE "landing_pages" ("id" integer primary key autoincrement not null, "template_id" varchar not null default '1', "event_id" integer not null, "show_language_selection" tinyint(1) not null default '0', "slug" varchar not null, "trackings" text, "customs" text, "orders" text, "align" varchar not null default 'center', "form_width" varchar default '1', "font" varchar, "languages" text, "banner_id" integer, "header_id" integer, "footer_id" integer, "bg_desktop_id" integer, "bg_tablet_id" integer, "bg_mobile_id" integer, "contact_name" varchar, "contact_phone" varchar, "contact_email" varchar, "contact_address" varchar, "status" varchar not null default 'NEW', "created_by" integer, "updated_by" integer, "created_at" datetime, "updated_at" datetime, "deleted_at" datetime, foreign key("event_id") references "events"("id"), foreign key("banner_id") references "media"("id"), foreign key("header_id") references "media"("id"), foreign key("footer_id") references "media"("id"), foreign key("bg_desktop_id") references "media"("id"), foreign key("bg_tablet_id") references "media"("id"), foreign key("bg_mobile_id") references "media"("id"), foreign key("created_by") references "users"("id"), foreign key("updated_by") references "users"("id"));
CREATE INDEX "landing_pages_created_by_index" on "landing_pages" ("created_by");
CREATE INDEX "landing_pages_event_id_index" on "landing_pages" ("event_id");
CREATE UNIQUE INDEX "landing_pages_event_id_slug_unique" on "landing_pages" ("event_id", "slug");
CREATE UNIQUE INDEX "landing_pages_slug_unique" on "landing_pages" ("slug");
CREATE INDEX "landing_pages_updated_by_index" on "landing_pages" ("updated_by");

-- Table: language_defines
CREATE TABLE "language_defines" ("id" integer primary key autoincrement not null, "event_id" integer not null, "language_id" integer not null, "keyword" varchar not null, "translate" text, "type" varchar not null default ('TEXT'), "value" text, "created_at" datetime, "updated_at" datetime, "status" varchar not null default ('ACTIVE'), foreign key("language_id") references languages("id") on delete no action on update no action, foreign key("event_id") references events("id") on delete no action on update no action);
CREATE INDEX "language_defines_event_id_index" on "language_defines" ("event_id");
CREATE INDEX "language_defines_language_id_index" on "language_defines" ("language_id");
CREATE UNIQUE INDEX "language_defines_language_id_keyword_unique" on "language_defines" ("language_id", "keyword");

-- Table: languages
CREATE TABLE "languages" ("id" integer primary key autoincrement not null, "name" varchar not null, "description" varchar not null, "status" varchar not null default 'ACTIVE', "created_at" datetime, "updated_at" datetime, "is_default" tinyint(1) not null default '0', "code" varchar, "icon_path" varchar);
CREATE UNIQUE INDEX "languages_name_unique" on "languages" ("name");

-- Table: likes
CREATE TABLE "likes" ("id" integer primary key autoincrement not null, "author_id" integer not null, "likeable_type" varchar, "likeable_id" integer, "created_at" datetime, "updated_at" datetime, foreign key("author_id") references "users"("id"));
CREATE INDEX "likes_likeable_type_likeable_id_index" on "likes" ("likeable_type", "likeable_id");

-- Table: lucky_draw_clients
CREATE TABLE "lucky_draw_clients" ("id" integer primary key autoincrement not null, "reward_id" integer, "lucky_draw_id" integer not null, "name" varchar not null, "qrcode" varchar not null, "email" varchar, "type" varchar default 'NEW', "custom_fields" text, "status" varchar not null default 'ACTIVE', "created_at" datetime, "updated_at" datetime, foreign key("reward_id") references "lucky_draw_rewards"("id"), foreign key("lucky_draw_id") references "lucky_draws"("id"));
CREATE INDEX "lucky_draw_clients_lucky_draw_id_index" on "lucky_draw_clients" ("lucky_draw_id");
CREATE INDEX "lucky_draw_clients_qrcode_index" on "lucky_draw_clients" ("qrcode");
CREATE INDEX "lucky_draw_clients_reward_id_index" on "lucky_draw_clients" ("reward_id");

-- Table: lucky_draw_layouts
CREATE TABLE "lucky_draw_layouts" ("id" integer primary key autoincrement not null, "lucky_draw_id" integer not null, "reward_id" integer, "name" varchar not null, "canvas_width" integer not null default '1920', "canvas_height" integer not null default '1080', "background_type" varchar check ("background_type" in ('color', 'image', 'video')) not null default 'color', "background_value" text, "blocks" text not null, "settings" text, "is_active" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime, foreign key("lucky_draw_id") references "lucky_draws"("id") on delete cascade, foreign key("reward_id") references "lucky_draw_rewards"("id") on delete cascade);
CREATE INDEX "lucky_draw_layouts_lucky_draw_id_index" on "lucky_draw_layouts" ("lucky_draw_id");
CREATE UNIQUE INDEX "lucky_draw_layouts_lucky_draw_id_reward_id_unique" on "lucky_draw_layouts" ("lucky_draw_id", "reward_id");
CREATE INDEX "lucky_draw_layouts_reward_id_index" on "lucky_draw_layouts" ("reward_id");

-- Table: lucky_draw_rewards
CREATE TABLE "lucky_draw_rewards" ("id" integer primary key autoincrement not null, "lucky_draw_id" integer, "is_given" tinyint(1) not null default '0', "code" varchar not null, "name" varchar not null, "img_link" varchar, "value" varchar, "order" integer, "order_name" varchar not null, "time" integer not null, "probability" float, "status" varchar not null default 'ACTIVE', "created_at" datetime, "updated_at" datetime, "assignee_id" integer, foreign key("lucky_draw_id") references "lucky_draws"("id"));
CREATE INDEX "lucky_draw_rewards_code_index" on "lucky_draw_rewards" ("code");
CREATE UNIQUE INDEX "lucky_draw_rewards_code_unique" on "lucky_draw_rewards" ("code");
CREATE INDEX "lucky_draw_rewards_lucky_draw_id_index" on "lucky_draw_rewards" ("lucky_draw_id");

-- Table: lucky_draws
CREATE TABLE "lucky_draws" ("id" integer primary key autoincrement not null, "event_id" integer, "name" varchar not null, "background_url_mobile" varchar, "background_url_desktop" varchar, "type" varchar not null default 'RAFFLE', "status" varchar not null default 'ACTIVE', "created_by" integer, "updated_by" integer, "created_at" datetime, "updated_at" datetime, "builder_settings" text, "field_mappings" text, "uploaded_reward_images" text, foreign key("event_id") references "events"("id"), foreign key("created_by") references "users"("id"), foreign key("updated_by") references "users"("id"));
CREATE INDEX "lucky_draws_created_by_index" on "lucky_draws" ("created_by");
CREATE INDEX "lucky_draws_event_id_index" on "lucky_draws" ("event_id");
CREATE INDEX "lucky_draws_updated_by_index" on "lucky_draws" ("updated_by");

-- Table: media
CREATE TABLE "media" ("id" integer primary key autoincrement not null, "model_type" varchar not null, "model_id" integer not null, "collection_name" varchar not null, "name" varchar not null, "file_name" varchar not null, "mime_type" varchar, "disk" varchar not null, "size" integer not null, "manipulations" text not null, "custom_properties" text not null, "responsive_images" text not null, "posted_at" datetime not null, "order_column" integer, "created_at" datetime, "updated_at" datetime, "uuid" varchar, "conversions_disk" varchar, "generated_conversions" text);
CREATE INDEX "media_model_type_model_id_index" on "media" ("model_type", "model_id");

-- Table: media_libraries
CREATE TABLE "media_libraries" ("id" integer primary key autoincrement not null, "created_at" datetime, "updated_at" datetime);

-- Table: migrations
CREATE TABLE "migrations" ("id" integer primary key autoincrement not null, "migration" varchar not null, "batch" integer not null);

-- Table: n8n_chat_messages
CREATE TABLE "n8n_chat_messages" ("id" integer primary key autoincrement not null, "session_id" integer not null, "user_id" integer, "role" varchar not null, "content" text not null, "content_html" text, "meta" text, "created_at" datetime, "updated_at" datetime, foreign key("session_id") references "n8n_chat_sessions"("id") on delete cascade);
CREATE INDEX "n8n_chat_messages_session_id_id_index" on "n8n_chat_messages" ("session_id", "id");
CREATE INDEX "n8n_chat_messages_user_id_id_index" on "n8n_chat_messages" ("user_id", "id");

-- Table: n8n_chat_sessions
CREATE TABLE "n8n_chat_sessions" ("id" integer primary key autoincrement not null, "user_id" integer not null, "status" varchar not null default 'ACTIVE', "mode" varchar not null default 'UNSET', "started_at" datetime, "closed_at" datetime, "created_at" datetime, "updated_at" datetime);
CREATE INDEX "n8n_chat_sessions_created_at_index" on "n8n_chat_sessions" ("created_at");
CREATE INDEX "n8n_chat_sessions_user_id_status_index" on "n8n_chat_sessions" ("user_id", "status");

-- Table: newsletter_subscriptions
CREATE TABLE "newsletter_subscriptions" ("id" integer primary key autoincrement not null, "email" varchar not null, "created_at" datetime, "updated_at" datetime);
CREATE UNIQUE INDEX "newsletter_subscriptions_email_unique" on "newsletter_subscriptions" ("email");

-- Table: orders
CREATE TABLE "orders" ("id" integer primary key autoincrement not null, "client_id" integer, "ref_id" integer, "no" varchar not null, "code" varchar, "token" varchar, "payment_url" varchar, "price" numeric not null default '0', "expiry_date" datetime not null, "ipn" text, "status" varchar not null default 'NEW', "created_at" datetime, "updated_at" datetime, foreign key("client_id") references "clients"("id") on delete set null on update cascade);
CREATE INDEX "orders_client_id_index" on "orders" ("client_id");

-- Table: packages
CREATE TABLE "packages" ("id" integer primary key autoincrement not null, "code" varchar not null, "created_at" datetime, "updated_at" datetime);
CREATE UNIQUE INDEX "packages_code_unique" on "packages" ("code");

-- Table: page_access_logs
CREATE TABLE "page_access_logs" ("id" integer primary key autoincrement not null, "lp_id" integer, "page" varchar not null, "ip_address" varchar, "user_id" integer, "created_at" datetime, "updated_at" datetime);
CREATE INDEX "page_access_logs_page_index" on "page_access_logs" ("page");

-- Table: password_resets
CREATE TABLE "password_resets" ("email" varchar not null, "token" varchar not null, "created_at" datetime);
CREATE INDEX "password_resets_email_index" on "password_resets" ("email");
CREATE INDEX "password_resets_token_index" on "password_resets" ("token");

-- Table: personal_access_tokens
CREATE TABLE "personal_access_tokens" ("id" integer primary key autoincrement not null, "tokenable_type" varchar not null, "tokenable_id" integer not null, "name" varchar not null, "token" varchar not null, "abilities" text, "last_used_at" datetime, "expires_at" datetime, "created_at" datetime, "updated_at" datetime);
CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens" ("token");
CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens" ("tokenable_type", "tokenable_id");

-- Table: persons
CREATE TABLE "persons" ("id" integer primary key autoincrement not null, "event_id" integer not null, "event_code" varchar, "company_name" varchar, "name" varchar not null, "code" varchar not null, "gender" varchar, "title" varchar, "email" varchar, "phone" varchar, "type" varchar, "status" varchar default 'ACTIVE', "created_at" datetime, "updated_at" datetime, "img_qrcode" varchar, foreign key("event_id") references "events"("id"));
CREATE INDEX "persons_event_id_index" on "persons" ("event_id");

-- Table: posts
CREATE TABLE "posts" ("id" integer primary key autoincrement not null, "author_id" integer not null default ('0'), "title" varchar not null, "content" text not null, "posted_at" datetime not null, "created_at" datetime, "updated_at" datetime, "slug" varchar not null, "thumbnail_id" integer, foreign key("author_id") references users("id") on delete cascade on update no action, foreign key("thumbnail_id") references "media"("id") on delete set null);
CREATE UNIQUE INDEX "posts_slug_unique" on "posts" ("slug");
CREATE INDEX "posts_title_index" on "posts" ("title");

-- Table: print_devices
CREATE TABLE "print_devices" ("id" integer primary key autoincrement not null, "printer_id" integer not null, "key" varchar not null, "name" varchar not null, "label_file_name" varchar not null, "ip_address" varchar not null, "url" varchar not null, "status" varchar not null default ('ACTIVE'), "created_at" datetime, "updated_at" datetime, "label_id" integer, foreign key("printer_id") references printers("id") on delete no action on update no action, foreign key("label_id") references "labels"("id"));
CREATE INDEX "print_devices_label_id_index" on "print_devices" ("label_id");
CREATE INDEX "print_devices_printer_id_index" on "print_devices" ("printer_id");

-- Table: print_logs
CREATE TABLE "print_logs" ("id" integer primary key autoincrement not null, "printer_id" integer not null, "file_path" varchar not null, "created_by" integer, "updated_by" integer, "type" varchar not null default 'NEW', "status" varchar not null default 'NEW', "created_at" datetime, "updated_at" datetime, foreign key("printer_id") references "printers"("id"), foreign key("created_by") references "users"("id"), foreign key("updated_by") references "users"("id"));
CREATE INDEX "print_logs_created_by_index" on "print_logs" ("created_by");
CREATE INDEX "print_logs_printer_id_index" on "print_logs" ("printer_id");
CREATE INDEX "print_logs_updated_by_index" on "print_logs" ("updated_by");

-- Table: printers
CREATE TABLE "printers" ("id" integer primary key autoincrement not null, "is_default" tinyint(1) not null default '0', "event_id" integer not null, "event_code" varchar not null, "name" varchar not null, "url" varchar not null, "printer_url" varchar not null, "printer" varchar not null, "label" varchar not null, "type" varchar not null default 'NEW', "status" varchar not null default 'NEW', "created_at" datetime, "updated_at" datetime, foreign key("event_id") references "events"("id"));
CREATE INDEX "printers_event_code_index" on "printers" ("event_code");
CREATE INDEX "printers_event_id_index" on "printers" ("event_id");

-- Table: provinces
CREATE TABLE "provinces" ("id" integer primary key autoincrement not null, "is_default" tinyint(1) not null default '0', "name" varchar not null, "created_at" datetime, "updated_at" datetime);

-- Table: role_user
CREATE TABLE "role_user" ("id" integer primary key autoincrement not null, "user_id" integer not null, "role_id" integer not null, "created_at" datetime, "updated_at" datetime, foreign key("user_id") references "users"("id"), foreign key("role_id") references "roles"("id"));

-- Table: roles
CREATE TABLE "roles" ("id" integer primary key autoincrement not null, "name" varchar not null, "created_at" datetime, "updated_at" datetime);

-- Table: sessions
CREATE TABLE "sessions" ("id" varchar not null, "user_id" integer, "ip_address" varchar, "user_agent" text, "payload" text not null, "last_activity" integer not null, primary key ("id"));
CREATE INDEX "sessions_last_activity_index" on "sessions" ("last_activity");
CREATE INDEX "sessions_user_id_index" on "sessions" ("user_id");

-- Table: settings
CREATE TABLE "settings" ("id" integer primary key autoincrement not null, "name" varchar not null, "value" text, "created_at" datetime, "updated_at" datetime);
CREATE UNIQUE INDEX "settings_name_unique" on "settings" ("name");

-- Table: smss
CREATE TABLE "smss" ("id" integer primary key autoincrement not null, "event_id" integer not null, "client_id" integer not null, "send_time" datetime, "status" varchar not null default 'NEW', "created_at" datetime, "updated_at" datetime, foreign key("event_id") references "events"("id"), foreign key("client_id") references "clients"("id"));
CREATE INDEX "smss_client_id_index" on "smss" ("client_id");
CREATE INDEX "smss_event_id_index" on "smss" ("event_id");

-- Table: summerizes
CREATE TABLE "summerizes" ("id" integer primary key autoincrement not null, "events" text, "clients" text, "status" varchar not null default 'ACTIVE', "created_by" integer, "updated_by" integer, "created_at" datetime, "updated_at" datetime, foreign key("created_by") references "users"("id"), foreign key("updated_by") references "users"("id"));

-- Table: tags
CREATE TABLE "tags" ("id" integer primary key autoincrement not null, "name" varchar, "status" varchar, "created_at" datetime, "updated_at" datetime);

-- Table: telescope_entries
CREATE TABLE "telescope_entries" ("sequence" integer primary key autoincrement not null, "uuid" varchar not null, "batch_id" varchar not null, "family_hash" varchar, "should_display_on_index" tinyint(1) not null default '1', "type" varchar not null, "content" text not null, "created_at" datetime);
CREATE INDEX "telescope_entries_batch_id_index" on "telescope_entries" ("batch_id");
CREATE INDEX "telescope_entries_created_at_index" on "telescope_entries" ("created_at");
CREATE INDEX "telescope_entries_family_hash_index" on "telescope_entries" ("family_hash");
CREATE INDEX "telescope_entries_type_should_display_on_index_index" on "telescope_entries" ("type", "should_display_on_index");
CREATE UNIQUE INDEX "telescope_entries_uuid_unique" on "telescope_entries" ("uuid");

-- Table: telescope_entries_tags
CREATE TABLE "telescope_entries_tags" ("entry_uuid" varchar not null, "tag" varchar not null, foreign key("entry_uuid") references "telescope_entries"("uuid") on delete cascade, primary key ("entry_uuid", "tag"));
CREATE INDEX "telescope_entries_tags_tag_index" on "telescope_entries_tags" ("tag");

-- Table: telescope_monitoring
CREATE TABLE "telescope_monitoring" ("tag" varchar not null, primary key ("tag"));

-- Table: tickets
CREATE TABLE "tickets" ("id" integer primary key autoincrement not null, "card_id" integer, "event_code" varchar not null, "code" varchar not null, "name" varchar, "type" varchar, "price" varchar not null, "dates_string" varchar, "dates_valid" text, "created_at" datetime, "updated_at" datetime, foreign key("card_id") references "cards"("id"));
CREATE INDEX "tickets_card_id_index" on "tickets" ("card_id");
CREATE INDEX "tickets_code_index" on "tickets" ("code");
CREATE UNIQUE INDEX "tickets_event_code_code_unique" on "tickets" ("event_code", "code");
CREATE INDEX "tickets_event_code_index" on "tickets" ("event_code");

-- Table: users
CREATE TABLE "users" ("id" integer primary key autoincrement not null, "is_admin" tinyint(1) not null default ('0'), "is_checkout" tinyint(1) not null default ('0'), "gender" integer not null default ('1'), "name" varchar not null, "email" varchar, "phone" varchar, "title" varchar, "position" varchar, "password" varchar, "last_login_at" datetime, "remember_token" varchar, "avatar" varchar, "created_at" datetime, "updated_at" datetime, "provider" varchar, "provider_id" varchar, "registered_at" datetime, "email_verified_at" datetime, "company_id" integer, "username" varchar not null, "permissions" text, "type" varchar not null default ('WEB'), "status" varchar not null default ('INACTIVE'), "event_id" integer, "expire_date" date, "created_by" integer, "updated_by" integer, "gate" varchar, "package_id" integer, "verify_token" varchar, "session_id" varchar, "must_accept_terms" tinyint(1) not null default '0', "terms_accepted_at" datetime, foreign key("updated_by") references users("id") on delete no action on update no action, foreign key("created_by") references users("id") on delete no action on update no action, foreign key("company_id") references companys("id") on delete set null on update no action, foreign key("event_id") references events("id") on delete set null on update no action, foreign key("package_id") references "packages"("id"));
CREATE UNIQUE INDEX "users_email_unique" on "users" ("email");
CREATE INDEX "users_package_id_index" on "users" ("package_id");
CREATE UNIQUE INDEX "users_provider_id_unique" on "users" ("provider_id");
CREATE UNIQUE INDEX "users_username_unique" on "users" ("username");
CREATE UNIQUE INDEX "users_verify_token_unique" on "users" ("verify_token");

-- Table: webhook_postmarks
CREATE TABLE "webhook_postmarks" ("id" integer primary key autoincrement not null, "ip_address" varchar, "server_id" varchar, "message_id" varchar not null, "message_stream" varchar not null, "email" varchar not null, "tag" varchar, "details" varchar, "record_time" datetime, "status" varchar not null, "metadata" text, "response" text, "created_at" datetime, "updated_at" datetime, "email_id" integer);
CREATE INDEX "webhook_postmarks_email_index" on "webhook_postmarks" ("email");
CREATE INDEX "webhook_postmarks_message_id_index" on "webhook_postmarks" ("message_id");
CREATE INDEX "webhook_postmarks_server_id_index" on "webhook_postmarks" ("server_id");
