<!-- Essential javaScripts for application to work-->
<script src="js/jquery-3.2.1.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
<!-- The javascript plugin to display page loading on top-->
<script src="js/plugins/pace.min.js"></script>
<!-- Page specific javaScripts-->
<script type="text/javascript" src="js/plugins/chart.js"></script>
<!-- Select2 plugin-->
<script type="text/javascript" src="js/plugins/select2.min.js"></script>
<script type="text/javascript">
    $('.select2').select2({
        placeholder: 'Select Stock',
        width: '100%',
        allowClear: true,
    });
</script>
<!-- Data table plugin-->
<script type="text/javascript" src="js/plugins/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/plugins/dataTables.bootstrap.min.js"></script>
<script type="text/javascript">$('#sampleTable').DataTable();</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#corporateInfo').DataTable({
            ordering: false,
            pageLength: 25,
        });
        $('.watchlistDataTable').DataTable({
            ordering: true,
            // order: [[ 1, "asc" ]]
            order: [],
            // columnDefs: [
            // {
            //     orderable: true,
            //     targets: [1]
            // } // Disable sorting on column index 1 (Price)
        //]
        });
        $('#indexTable').DataTable({
            ordering: false,
        });
    });

    var data = {
    labels: ["January", "February", "March", "April", "May"],
    datasets: [
        {
            label: "My First dataset",
            fillColor: "rgba(220,220,220,0.2)",
            strokeColor: "rgba(220,220,220,1)",
            pointColor: "rgba(220,220,220,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(220,220,220,1)",
            data: [65, 59, 80, 81, 56]
        },
        {
            label: "My Second dataset",
            fillColor: "rgba(151,187,205,0.2)",
            strokeColor: "rgba(151,187,205,1)",
            pointColor: "rgba(151,187,205,1)",
            pointStrokeColor: "#fff",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(151,187,205,1)",
            data: [28, 48, 40, 19, 86]
        }
    ]
    };
    var pdata = [
    {
        value: 300,
        color: "#46BFBD",
        highlight: "#5AD3D1",
        label: "Complete"
    },
    {
        value: 50,
        color:"#F7464A",
        highlight: "#FF5A5E",
        label: "In-Progress"
    }
    ]
    
    var ctxl = $("#lineChartDemo").get(0).getContext("2d");
    var lineChart = new Chart(ctxl).Line(data);
    
    var ctxp = $("#pieChartDemo").get(0).getContext("2d");
    var pieChart = new Chart(ctxp).Pie(pdata);
</script>
<!-- Google analytics script-->
<script type="text/javascript">
    if(document.location.hostname == 'pratikborsadiya.in') {
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-72504830-1', 'auto');
    ga('send', 'pageview');
    }
</script>
