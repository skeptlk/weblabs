
$(() => {
    $('#upload').submit(upload);
    $('#gen_report').submit(getReport);
});

var timeout;

function upload(e) {
    e.preventDefault();
    const formData = new FormData(this);

    $.ajax({
        url: '/upload', 
        type: 'POST',
        data: formData, 
        processData: false,
        contentType: false,
        success: () => { 
            hideProgressBar(); 
            clearTimeout(timeout); 
            alert('Successfully uploaded!');
        }
    });

    showProgressBar();
    timeout = setTimeout(updateStatus, 300);
}

function updateStatus() {
    $.get('/status', {}, (resp) => {
        var percent = +resp;
        setProgressBarVal(percent);
        if (percent > 99.9) {
            clearTimeout(timeout);
            hideProgressBar();
        }
    });
    timeout = setTimeout(updateStatus, 300);
}

function getReport () {
    $.get('/report', $(this).serialize(), (data) => showReport(JSON.parse(data)));
    return false;
}

function showReport (data) {
    const groupby = $('#groupby').val();
    let report = '<h3>Report:</h3>';
    report += '<table class="table">';
    report += '<thead>';
    report +=   '<tr>';
    report +=      `<th>${groupby}</th>`;
    report +=      '<th>Average downloaded size per session</th>';
    report +=   '</tr>';
    report += '</thead>';
    report += '<tbody>';

    for (let row of data) {
        const key = row[groupby];
        const val = (+row['avg_size'] / 1024).toFixed(2);
        let dataRow = `<tr><td>${key}</td><td>${val} MB</td></tr>`;
        report += dataRow;
    }

    report += '</tbody>';

    $("#table").empty();
    $("#table").append(report);

    drawPlot(data, groupby, 'avg_size');
}

function drawPlot (data, k_name, v_name) {
    let plotData = data.map((el) => [+el[k_name], +el[v_name]]);
    const options = {
        series: {
            bars: { 
                show: true, 
                fillColor: 'rgba(145, 214, 255, 0.75)', 
                lineWidth: 0 
            }
        }
    };
    $.plot($("#plot"), [ plotData ], options);
}

function setProgressBarVal(percent) {
    $('.progress-bar').css("width", percent + "%");
}

function showProgressBar() {
    $('.spinner-border').show();
    $('.progress').show();
}

function hideProgressBar() {
    $('.spinner-border').hide();
    $('.progress').hide();
}
