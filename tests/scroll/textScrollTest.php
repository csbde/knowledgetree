<?php

require_once("../../config/dmsDefaults.php");

function renderPage() {
    global $default;
    
   	//$sToRender =  "<table border=\"0\" width=\"100%\">\n";
    /*
    $sToRender .= "    <div id=\"divUp\">";
    $sToRender .= "        <a href=\"#\" onmouseover=\"scroll(-7)\" onmouseout=\"noScroll()\"><img src=\"" . $default->system->get("graphicsUrl") . "/desc.gif\" width=\"14\" height=\"14\" alt=\"up\" border=\"0\"></a>";
    $sToRender .= "    </div>";
    $sToRender .= "    <div id=\"divDown\">";
    $sToRender .= "        <a href=\"#\" onmouseover=\"scroll(7)\" onmouseout=\"noScroll()\"><img src=\"" . $default->system->get("graphicsUrl") . "/asc.gif\" width=\"14\" height=\"14\" alt=\"down\" border=\"0\"></a>";
    $sToRender .= "    </div>";*/
    //$sToRender = "    <tr><td>";
    /*$sToRender .= "        <div id=\"divScrollTextCont\">";
    $sToRender .= "            <div id=\"divText\">";*/    
    $sToRender = "<table border=1><tr><td>Subject: IDIOTS OF LIFE

Recently, when I went to McDonald's I saw on the menu that you could have
an order of 6, 9 or 12 Chicken McNuggets. I asked for a half dozen nuggets.
\"We don't have half dozen nuggets,\" said the teenager at the counter.\"You
don't?\" I replied. \"We only have six, nine, or  twelve,\"  was the reply.
\"So I can't order a half-dozen nuggets, but I can order six?\"  \"That's
right.\" So I shook my head and ordered six McNuggets.

The paragraph above doesn't amaze me because of what happened a couple of
months ago. I was checking out at the local Foodland with just a few items
and the lady behind me put her things on the belt close to mine. I picked
up one of those \"Dividers\" that they keep by the cash register and placed
it between our things so they wouldn't get mixed.  After the girl had
scanned all of my items, she picked up the \"Divider\"  looking it all over
for the bar code so she could scan it. Not finding the bar code she said to
me \"Do you know how much this is?\" and I said to her\" I've changed my mind,
I don't think I'll buy that today.\" She said \"OK\" and I paid her for the
things and left. She had no clue to what had just
happened...

A lady at work was seen putting a credit card into her floppy drive and
pulling it out very quickly. When inquired as to what she was doing, she
said she was shopping on the Internet and they kept asking for a credit
card number, so she was using the ATM thingy\".

I recently saw a distraught young lady weeping beside her car. Do you need
some help?\" I asked. She replied, \"I knew I should have replaced the
battery to this remote door unlocker. Now I can't get into my car. Do you
think they ( pointing to a distant convenient store) would have a battery
to fit this?\" \"Hmmm, I dunno. Do you have an alarm too?\" I asked. \"No, just
this remote thingy,\" she answered, handing it and the car keys to me. As I
took the key and manually unlocked the door, I replied, \"Why don't you
drive over
there and check about the batteries. It's a long walk.\"

Several years ago, we had an Intern who was none too swift. One day she was
typing and turned to a secretary and said, \"I'm almost out of typing paper.
What do I do?\" \"Just use copier machine paper,\" the secretary told her.
With that, the intern took her last remaining blank piece of paper, put it
on the photocopier and proceeded to make five \"blank\" copies.

I was in a car dealership a while ago, when a large motor home was towed
into the garage. The front of the vehicle was in dire need of repair and
the whole thing generally looked like an extra in Twister.\" I asked the
manager what had happened. He told me that the driver had set the \"cruise
control\" and then went in the back to make a sandwich.

IDIOTS & COMPUTERS...
My neighbor works in the operations department in the central office of a
large bank. Employees in the field call him when they have problems with
their computers. One night he got a call from a woman in one of the branch
banks who had this question: \"I've got smoke coming from the back of my
terminal. Do you guys have a fire downtown?\"

IDIOTS ARE EASY TO PLEASE:
I was sitting in my science class, when the teacher commented that the next
day would be the shortest day of the year. My lab partner became visibly
excited, cheering and clapping. I explained to her that the amount of
daylight changes, not the actual amount of time. Needless to say, she was
very disappointed.

Police in Radnor, Pennsylvania, interrogated a suspect by placing a metal
colander on his head and connecting it with wires to a photocopy machine.
The message \"He's lying\" was placed in the copier, and police pressed the
copy button each time they thought the suspect wasn't telling the truth.
Believing the \"lie detector\" was working, the suspect confessed.  \"Life is
tough. It's tougher if you're stupid.\"</td></tr></table>";
    //$sToRender .= "</div></div></tr></td>";
    //$sToRender .= "</tr></td>";
    //$sToRender .= "\t</table>";

    return $sToRender;
}

 
// -------------------------------
// page start
// -------------------------------

// only if we have a valid session
if (checkSession()) {
    require_once("../../presentation/webpageTemplate.inc");
    
    // instantiate my content pattern
    $oContent = new PatternCustom();
    
    // set the content
    $oContent->addHtml(renderPage());
    $main->setCentralPayload($oContent);
    $main->setFormAction($_SERVER["PHP_SELF"]);
    $main->render();
    
} else {
    // redirect to no permission page
    redirect("$default->uiUrl/noAccess.php");
}
?>
