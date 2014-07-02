/**
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* Copyright 2014 Akvelon Inc.
* http://www.akvelon.com/contact-us
*/

/* Disable all flavor params */ 
UPDATE flavor_params SET
	deleted_at = now();

UPDATE flavor_params SET
		deleted_at = NULL
WHERE
	(name = 'Source') or (name = 'Default Thumbnail');

/* set default convesion profile */
DELETE FROM flavor_params_conversion_profile;

INSERT INTO flavor_params_conversion_profile SET
	conversion_profile_id = 1,
	flavor_params_id = (SELECT id FROM flavor_params WHERE tags = 'source'),
	system_name = 'Source',
	origin = 1,
	force_none_complied = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params_conversion_profile SET
	conversion_profile_id = 1,
	flavor_params_id = (SELECT id FROM flavor_params WHERE tags = 'default_thumb'),
	system_name = '',
	origin = 0,
	force_none_complied = 0,
	created_at = now(),
	updated_at = now();

/* Add new flavor params */

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set 1080p (6000)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-1080p-(6000)',
	tags = 'web,mbr',
	description = 'HD/1080 - WEB (H264/6000)',
	format = 'mp4',
	video_codec = 'h264h',
	width = 1920,
	height = 1080,
	video_bitrate = 6000,
	audio_codec = 'aac',
	audio_bitrate = 128,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set 1080p (4700)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-1080p-(4700)',
	tags = 'web,mbr',
	description = 'HD/1080 - WEB (H264/4700)',
	format = 'mp4',
	video_codec = 'h264h',
	width = 1920,
	height = 1080,
	video_bitrate = 4700,
	audio_codec = 'aac',
	audio_bitrate = 128,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set 720p (3400)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-720p-(3400)',
	tags = 'web,mbr',
	description = 'HD/720 - WEB (H264/3400)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 1280,
	height = 720,
	video_bitrate = 3400,
	audio_codec = 'aac',
	audio_bitrate = 128,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set 720p (2250)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-720p-(2250)',
	tags = 'mobile,web,mbr,ipad',
	description = 'SD/Large - WEB/MBL (H264/2250)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 960,
	height = 540,
	video_bitrate = 2250,
	audio_codec = 'aac',
	audio_bitrate = 128,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set 720p (1500)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-720p-(1500)',
	tags = 'mobile,web,mbr,ipad',
	description = 'SD/Large - WEB/MBL (H264/1500)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 960,
	height = 540,
	video_bitrate = 1500,
	audio_codec = 'aac',
	audio_bitrate = 128,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set 720p (1000)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-720p-(1000)',
	tags = 'mobile,web,mbr,ipad',
	description = 'SD/Large - WEB/MBL (H264/1000)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 640,
	height = 360,
	video_bitrate = 1000,
	audio_codec = 'aac',
	audio_bitrate = 128,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set 720p (650)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-720p-(650)',
	tags = 'mobile,web,mbr,ipad,iphone',
	description = 'Basic/Large - WEB/MBL (H264/650)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 640,
	height = 360,
	video_bitrate = 650,
	audio_codec = 'aac',
	audio_bitrate = 128,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set 720p (400)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-720p-(400)',
	tags = 'mobile,web,mbr,ipad,iphone',
	description = 'Basic/Large - WEB/MBL (H264/400)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 320,
	height = 180,
	video_bitrate = 400,
	audio_codec = 'aac',
	audio_bitrate = 128,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

/* add to default conversion profile */
INSERT INTO flavor_params_conversion_profile SET
	conversion_profile_id = 1,
	flavor_params_id = (SELECT LAST_INSERT_ID()),
	system_name = '',
	origin = 0,
	force_none_complied = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set SD 16x9 (1900)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-SD-16x9-(1900)',
	tags = 'mobile,web,mbr,ipad',
	description = 'SD/Large - WEB/MBL (H264/1900)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 848,
	height = 480,
	video_bitrate = 1900,
	audio_codec = 'aac',
	audio_bitrate = 96,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set SD 16x9 (1300)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-SD-16x9-(1300)',
	tags = 'mobile,web,mbr,ipad',
	description = 'SD/Large - WEB/MBL (H264/1300)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 848,
	height = 480,
	video_bitrate = 1300,
	audio_codec = 'aac',
	audio_bitrate = 96,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set SD 16x9 (900)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-SD-16x9-(900)',
	tags = 'mobile,web,mbr,ipad',
	description = 'SD/Large - WEB/MBL (H264/900)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 636,
	height = 360,
	video_bitrate = 900,
	audio_codec = 'aac',
	audio_bitrate = 96,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set SD 16x9 (650)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-SD-16x9-(650)',
	tags = 'mobile,web,mbr,ipad,iphone',
	description = 'Basic/Large - WEB/MBL (H264/650)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 636,
	height = 360,
	video_bitrate = 650,
	audio_codec = 'aac',
	audio_bitrate = 96,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set SD 16x9 (400)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-SD-16x9-(400)',
	tags = 'mobile,web,mbr,ipad,iphone',
	description = 'Basic/Large - WEB/MBL (H264/400)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 424,
	height = 240,
	video_bitrate = 400,
	audio_codec = 'aac',
	audio_bitrate = 96,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set SD 4x3 (1900)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-SD-4x3-(1900)',
	tags = 'mobile,web,mbr,ipad',
	description = 'SD/Small-WEB/MBL(H264/1900)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 640,
	height = 480,
	video_bitrate = 1900,
	audio_codec = 'aac',
	audio_bitrate = 96,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set SD 4x3 (1300)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-SD-4x3-(1300)',
	tags = 'mobile,web,mbr,ipad',
	description = 'SD/Small-WEB/MBL(H264/1300)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 640,
	height = 480,
	video_bitrate = 1300,
	audio_codec = 'aac',
	audio_bitrate = 96,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set SD 4x3 (900)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-SD-4x3-(900)',
	tags = 'mobile,web,mbr,ipad',
	description = 'SD/Small-WEB/MBL(H264/900)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 480,
	height = 360,
	video_bitrate = 900,
	audio_codec = 'aac',
	audio_bitrate = 96,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set SD 4x3 (650)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-SD-4x3-(650)',
	tags = 'mobile,web,mbr,ipad,iphone',
	description = 'Basic/Small-WEB/MBL(H264/650)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 480,
	height = 360,
	video_bitrate = 650,
	audio_codec = 'aac',
	audio_bitrate = 96,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Adaptive Bitrate MP4 Set SD 4x3 (400)',
	system_name = 'H264-Adaptive-Bitrate-MP4-Set-SD-4x3-(400)',
	tags = 'mobile,web,mbr,ipad,iphone',
	description = 'Basic/Small-WEB/MBL(H264/400)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 320,
	height = 240,
	video_bitrate = 400,
	audio_codec = 'aac',
	audio_bitrate = 96,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Broadband 1080p',
	system_name = 'H264-Broadband-1080p',
	tags = 'web,mbr',
	description = 'HD/1080 - WEB (H264/6750)',
	format = 'mp4',
	video_codec = 'h264h',
	width = 1920,
	height = 1080,
	video_bitrate = 6750,
	audio_codec = 'aac',
	audio_bitrate = 128,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Broadband 720p',
	system_name = 'H264-Broadband-720p',
	tags = 'web,mbr',
	description = 'HD/720 - WEB (H264/4500)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 1280,
	height = 720,
	video_bitrate = 4500,
	audio_codec = 'aac',
	audio_bitrate = 128,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Broadband SD 16x9',
	system_name = 'H264-Broadband-SD-16x9',
	tags = 'mobile,web,mbr,ipad',
	description = 'SD/Large - WEB/MBL (H264/2200)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 852,
	height = 480,
	video_bitrate = 2200,
	audio_codec = 'aac',
	audio_bitrate = 128,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

INSERT INTO flavor_params SET
	name = 'H264 Broadband SD 4x3',
	system_name = 'H264-Broadband-SD-4x3',
	tags = 'mobile,web,mbr,ipad',
	description = 'SD/Small-WEB/MBL(H264/1800)',
	format = 'mp4',
	video_codec = 'h264m',
	width = 640,
	height = 480,
	video_bitrate = 1800,
	audio_codec = 'aac',
	audio_bitrate = 128,
	audio_channels = 2,
	audio_sample_rate = 44100,
	conversion_engines = '8',
	version = 0,
	partner_id = 0,
	ready_behavior = 2,
	is_default = 1,
	view_order = 0,
	creation_mode = 1,
	deinterlice = 0,
	rotate = 0,
	type = 1,
	audio_resolution = 0,
	frame_rate = 0,
	gop_size = 0,
	two_pass = 0,
	created_at = now(),
	updated_at = now();

