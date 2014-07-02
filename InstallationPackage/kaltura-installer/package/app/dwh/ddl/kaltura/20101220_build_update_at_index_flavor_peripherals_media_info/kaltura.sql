use kaltura;

create index updated_at_index
on kaltura.media_info(updated_at);

create index updated_at_index
on kaltura.flavor_params_conversion_profile(updated_at);

create index updated_at_index
on kaltura.conversion_profile_2(updated_at);

create index updated_at_index
on kaltura.flavor_params_output(updated_at);