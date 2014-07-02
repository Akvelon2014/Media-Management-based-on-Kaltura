use kaltura;

create index updated_at_index
on kaltura.file_sync(updated_at);

create index updated_at_index
on kaltura.flavor_asset(updated_at);

create index updated_at_index
on kaltura.flavor_params(updated_at);