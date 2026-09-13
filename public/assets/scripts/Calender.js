$('#example').bsCalendar({
    width: '100%',
});

$(document).ready(function () {
    $('#calendarTwo').evoCalendar({
        settingName: "hello"
    })
    calendarEvents: [
        {
            id: 'mmnnn',
            name: 'new year',
            date: 'January/1/2020',
            type: 'holiday',
            everyYear: true

        },
        {
            id: '0908',
            name: 'new year',
            date: 'January/1/2020',
            type: 'holiday',
            everyYear: true

        }
    ]
})

    // TODO: Replace with real events, say from server.
    var sameDaylastWeek = new Date().setDate(new Date().getDate() - 7);
		var someDaynextMonth = new Date().setDate(new Date().getDate() + 31);

		// All dates should be provided in timestamps
	    	var sampleEvents = [
			{
			    title: "Soulful sundays bay area",
			    date: sameDaylastWeek, // Same day as today, last week
			},
			{
			    title: "London Comicon",
			    date: new Date().getTime(), // Today
			},
			{
			    title: "Youth Athletic Camp",
			    date: someDaynextMonth, // Some day as today, next month
			}
	    	];

    $(document).ready(function(){
        $("#calendar").MEC({
            events: sampleEvents
        });

        // Make calendar start on monday
        $("#calendar2").MEC({
            from_monday: true,
            events: sampleEvents
        });
    });
    