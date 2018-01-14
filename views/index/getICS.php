<?php
// Hide Debug toolbar if enabled
if (class_exists('yii\debug\Module')) {
    $this->off(\yii\web\View::EVENT_END_BODY, [\yii\debug\Module::getInstance(), 'renderToolbar']);
}
?>
<?php
$link = $contentContainer->createUrl('/task/index/view', array('id' => $task->id), true);
$uid = uniqid();
$title = $task->title;
$description = Yii::t('TaskModule.views_index_getICS', 'Task details: %link%', array('%link%' => $link));
$location = $task->location . " " . $task->room;
$organizer = $task->content->user->displayName;
$organizerMail = $task->content->user->email;
$begin = Yii::$app->formatter->asDate($task->date, "yyyyMMdd") . "T" . Yii::$app->formatter->asTime($task->begin, "HHmmss");
$end = Yii::$app->formatter->asDate($task->date, "yyyyMMdd") . "T" . Yii::$app->formatter->asTime($task->end, "HHmmss");

$attendee = "";
foreach ($task->participants as $participant) {
    if ($participant->user->id == $task->content->user->id)
        continue;
    $attendee .= "ATTENDEE;CN=" . $participant->user->displayName . ":MAILTO:" . $participant->user->email . "\n";
}
?>
<?php
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $title . '.ics"');
?>
<?php if ($type == "private") : ?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:<?php echo $link; ?> 
BEGIN:VEVENT
UID:HumHub-<?php echo $uid; ?> 
DTSTART;TZID=Europe/Berlin:<?php echo $begin; ?> 
DTEND;TZID=Europe/Berlin:<?php echo $end; ?> 
SUMMARY:<?php echo $title; ?> 
LOCATION:<?php echo $location; ?> 
STATUS:CONFIRMED
DESCRIPTION:<?php echo $description; ?> 
END:VEVENT
END:VCALENDAR
<?php else : ?> 
BEGIN:VCALENDAR
VERSION:2.0
PRODID:<?php echo $link; ?> 
BEGIN:VEVENT
UID:HumHub-<?php echo $uid; ?> 
DTSTART;TZID=Europe/Berlin:<?php echo $begin; ?> 
DTEND;TZID=Europe/Berlin:<?php echo $end; ?> 
SUMMARY:<?php echo $title; ?> 
LOCATION:<?php echo $location; ?> 
DESCRIPTION:<?php echo $description; ?> 
ORGANIZER;CN="<?php echo $organizer; ?>":MAILTO:<?php echo $organizerMail; ?> 
<?php echo $attendee; ?> 
END:VEVENT
END:VCALENDAR
<?php endif; ?> 
