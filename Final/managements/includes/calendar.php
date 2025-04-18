<?php
// Fetch job card events for the calendar
require_once 'JobCard_Management/config/db_connection.php';
try {
  // SQL query to fetch job card events with customer and car information
  $sql = "SELECT j.JobID, j.DateStart, j.DateFinish,
          CONCAT(c.FirstName, ' ', c.LastName) as CustomerName,
          car.Brand, car.Model
          FROM jobcards j 
          LEFT JOIN jobcar jc ON j.JobID = jc.JobID
          LEFT JOIN cars car ON jc.LicenseNr = car.LicenseNr
          LEFT JOIN carassoc ca ON car.LicenseNr = ca.LicenseNr
          LEFT JOIN customers c ON ca.CustomerID = c.CustomerID
          WHERE j.DateStart IS NOT NULL
          ORDER BY j.DateStart DESC";
  
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $jobCardEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
  // Convert job card events to calendar events format
  $calendarEvents = array_map(function($event) {
    // Format car information
    $carInfo = '';
    if (!empty($event['Brand']) || !empty($event['Model'])) {
      $carInfo = trim($event['Brand'] . ' ' . $event['Model']);
    }
    
    // Create event title with customer and car info
    $title = $event['CustomerName'];
    if (!empty($carInfo)) {
      $title .= ' - ' . $carInfo;
    }
    
    // Determine if job is closed (has end date)
    $isClosed = !empty($event['DateFinish']);
    
    return [
      'id' => $event['JobID'],
      'title' => $title,
      'start' => $event['DateStart'],
      'allDay' => true,
      'backgroundColor' => $isClosed ? '#DC2626' : '#059669', // Red for closed, Green for open
      'borderColor' => $isClosed ? '#DC2626' : '#059669', // Red for closed, Green for open
      'extendedProps' => [
        'customerName' => $event['CustomerName'],
        'carInfo' => $carInfo,
        'status' => $isClosed ? 'Closed' : 'Open',
        'jobId' => $event['JobID'] // Add jobId to extendedProps for easy access
      ]
    ];
  }, $jobCardEvents);
} catch (PDOException $e) {
  error_log("Database error: " . $e->getMessage());
  $calendarEvents = [];
}
?>

<!-- Calendar Widget -->
<div class="col-md-8">
  <div class="widget-container">
    <div id="calendar"></div>
  </div>
</div>

<style>
  /* Calendar Styles */
  .calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
  }

  .calendar-header h5 {
    font-weight: 600;
    color: #111827;
    font-size: 1.25rem;
    margin: 0;
  }

  #calendar {
    min-height: 400px;
  }

  .fc {
    background: var(--card-background);
    border-radius: var(--border-radius);
  }

  .fc .fc-toolbar-title {
    font-size: 1.2em;
    font-weight: 600;
    color: #111827;
  }

  .btn-primary {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
    border-radius: 9px;
  }

  .fc .fc-event {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    cursor: pointer;
    border-radius: 4px;
    padding: 1px 2px;
  }

  .fc .fc-event:hover {
    background-color: var(--primary-light);
    border-color: var(--primary-light);
  }

  /* Mobile Responsive Styles */
  @media (max-width: 768px) {
    #calendar {
      min-height: 300px;
    }

    .fc .fc-toolbar {
      flex-direction: column;
      gap: 10px;
    }

    .fc .fc-toolbar-title {
      font-size: 1.1em;
    }

    .fc .fc-button {
      padding: 0.4rem 0.8rem;
      font-size: 0.9em;
    }

    .fc .fc-event {
      font-size: 0.85em;
      padding: 1px 3px;
    }

    .fc .fc-daygrid-day {
      min-height: 80px;
    }

    .fc .fc-daygrid-day-number {
      padding: 2px 4px;
      font-size: 0.9em;
    }

    .fc .fc-daygrid-more-link {
      font-size: 0.85em;
    }

    .widget-container {
      padding: 15px;
    }

    .calendar-header {
      margin-bottom: 15px;
    }

    .calendar-header h5 {
      font-size: 1.1rem;
    }
  }

  /* Small Mobile Devices */
  @media (max-width: 480px) {
    #calendar {
      min-height: 250px;
    }

    .fc .fc-toolbar-title {
      font-size: 1em;
    }

    .fc .fc-button {
      padding: 0.3rem 0.6rem;
      font-size: 0.8em;
    }

    .fc .fc-event {
      font-size: 0.8em;
    }

    .fc .fc-daygrid-day {
      min-height: 60px;
    }
  }

  .col-md-6 {
    max-width: 100%;
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize calendar element
  var calendarEl = document.getElementById('calendar');
  
  if (!calendarEl) {
    console.error('Calendar element not found!');
    return;
  }

  try {
    // Create and configure FullCalendar instance
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      headerToolbar: {
        // Calendar header controls
      },
      themeSystem: 'bootstrap',
      height: 'auto',
      events: <?php echo json_encode($calendarEvents); ?>,
      
      // Handle event click
      eventClick: function(info) {
        const jobId = info.event.extendedProps.jobId;
        // First check if we're in JobCard_Management
        if (!window.location.pathname.includes('JobCard_Management')) {
          // If not, navigate to JobCard_Management first
          window.location.href = 'JobCard_Management/views/job_cards_main.php';
          // Store the jobId to load after navigation
          sessionStorage.setItem('pendingJobId', jobId);
        } else {
          // If already in JobCard_Management, load the view directly
          $.get('JobCard_Management/views/job_card_view.php', { id: jobId }, function(response) {
            $('#dynamicContent').html(response);
          });
        }
      },
      
      // Add tooltips to events
      eventDidMount: function(info) {
        // Create tooltip content
        var event = info.event;
        var tooltipContent = `
          <div class="tooltip-content">
            <strong>${event.extendedProps.customerName}</strong><br>
            ${event.extendedProps.carInfo}<br>
            <span style="color: ${event.extendedProps.status === 'Closed' ? '#DC2626' : '#059669'}">
              ${event.extendedProps.status}
            </span>
          </div>
        `;
        
        // Initialize Bootstrap tooltip
        $(info.el).tooltip({
          title: tooltipContent,
          html: true,
          placement: 'top',
          trigger: 'hover',
          container: 'body'
        });
      },
      
      // Calendar configuration
      displayEventTime: false,
      firstDay: 1, // Start week on Monday
      weekNumbers: false,
      weekText: 'W',
      dayMaxEvents: true,
      moreLinkText: function(n) {
        return '+more ' + n;
      }
    });
    
    // Render the calendar
    calendar.render();
  } catch (error) {
    console.error('Error creating calendar:', error);
  }
});
</script> 