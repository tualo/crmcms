set foreign_key_checks=0;
alter table  page_content_middlewares modify middleware varchar(255);
alter table cms_middlewares modify id varchar(255);
set foreign_key_checks=1;
