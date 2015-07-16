#!/usr/bin/perl

# run using crontab every 5 minutes past the hour from 9.05am - 1.05am
# 5 0,1,9-23 * * * /home/xbmc/notifyDelay.sh
use lib "/home/temperec/perl5/lib/perl5";

use HTML::TreeBuilder;

$current_time = trim(`date`);
open(OUTFILE, ">> /home/temperec/delayboard.txt") || die "can't open file!";
print OUTFILE "Querying live arrivals board at ", $current_time, "\n";

check_late_trains->("http://ojp.nationalrail.co.uk/service/ldbboard/arr/KGX");
check_late_trains->("http://ojp.nationalrail.co.uk/service/ldbboard/arr/EDB");

# polls the lookup address and prints any relevant late running trains to OUTFILE
# param1 : address of live arrivals board
sub check_late_trains {

    my $lookup_addr = shift;

	my $arr_board = `curl $lookup_addr`;

	if($? != 0) {
		$arr_board = ""; #`curl $lookup_addr 2>&1`; # try it again, this time capture stderr
		print OUTFILE "Error occurred. ", $arr_board, "\n"; 
		return;
	}

	$tree = HTML::TreeBuilder->new;
	$tree->parse($arr_board);
	print OUTFILE "Finished parsing ", trim(`date`), "\n";

	# for each row, print the departure station and delay
	for my $tr ($tree->look_down( "_tag" => "tr", "class" => qr/.*delayed/ )) {

		my $dep_stn;
		for my $dep($tr->look_down( "_tag" => "td", "class" => "destination")) {
			$dep_stn = trim($dep->as_text);
		}

		my @cols = $tr->content_list;
		if(is_interested_station->($dep_stn)) {
			print OUTFILE "    Arriving from ", $dep_stn, " was due at ", $cols[0]->as_text, ", now expected at ", $cols[2]->as_text, "\n";
		}
	}
}

# removes whitespace from beginning/end of string
sub trim {
    my $result = shift;
    $result =~ s/^\s+//;
    $result =~ s/\s+$//;
    return $result;
}

# these are the stations we are interested in
sub is_interested_station {
    my $stn = shift;
    if(($stn eq "Edinburgh") or ($stn eq "Aberdeen") or ($stn eq "Inverness") or ($stn eq "London Kings Cross")) {
        return 1;
    } else {
        return 0;
    }
}
