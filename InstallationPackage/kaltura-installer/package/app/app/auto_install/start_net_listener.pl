#!/usr/bin/perl

use strict;
use warnings;

use IO::Socket;
use IO::Socket::INET;
use POSIX 'strftime'; # Need to explicitly load the functions in POSIX

if ($#ARGV <0){
	die "Usage: $0 <port>";
}
my ($port)=@ARGV;
# Note that if you pass no argument to localtime, it assumes the current time
my $DateTime = strftime '%Y-%m-%d-%H:%M:%S', localtime;

# ----------------------------------------------------------------------------------
print "\nStarting listener on port $port... $DateTime\n\n";
# ----------------------------------------------------------------------------------

my $count = 1;
my $limit = 100;

while ($count <= $limit){
	my $sock = new IO::Socket::INET (
	LocalPort => $port,
	Proto => 'tcp',
	Listen => SOMAXCONN,
	ReusePort => 1
     );
     
     die "Could not create socket: $!\n" unless $sock;

     my $new_sock = $sock->accept();
     while(<$new_sock>) {
	s/ //g;
	chop $_;
	my $DateTime = strftime '%Y-%m-%d-%H:%M:%S', localtime;
	print "$count -- $DateTime -- $_ \n";
     }
     
     close($sock);

$count++;
}
