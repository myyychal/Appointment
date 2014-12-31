create table users (
	login varchar primary key,
	password varchar not null,
	privilege integer not null
);

create table meetings (
	name varchar primary key,
	startday varchar not null,
	starthour varchar not null,
	endhour varchar not null,
	sectionlength integer not null
);

create table userscurves (
	userlogin varchar not null,
	meetingname varchar not null,
	points varchar not null,
	foreign key (userlogin) references users(login) on delete cascade,
	foreign key (meetingname) references meetings(name) on delete cascade,
	primary key (userlogin, meetingname)
);