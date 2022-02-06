<?php

namespace App\HelpRequests;

use App\Entity\Agronomist;
use App\Entity\Farmer;
use App\Entity\HelpRequest\HelpReply;
use App\Entity\HelpRequest\HelpRequest;
use App\Entity\User;
use DateTime;
use Exception;

class HelpRequestsService
{
    /** Creates a new help request, timestamped with the present moment.
     * @param Farmer $author the author of the created help request
     * @param User $receiver the receiver of the created help request (either an agronomist or a best-performing farmer)
     * @param string $title the title of the created help request (not longer than 50 characters)
     * @param string $text the text of the created hep request (not longer than 1500 characters)
     * @return HelpRequest the new help request object created
     * @throws Exception if the receiver is not an agronomist or a best-performing farmer
     */
    public function createHelpRequest(Farmer $author, User $receiver, string $title, string $text): HelpRequest
    {
        if (!($receiver->isAgronomist() || ($receiver->isFarmer() && $receiver->getBestPerforming()))) {
            throw new Exception('invalid receiver');
        }
        return new HelpRequest(new DateTime(), $title, $text, $author, $receiver);
    }

    /**
     * Creates a new help reply, timestamped with the present moment.
     * @param string $text the text of the created help reply
     * @param HelpRequest $helpRequest the help request which the created help reply is related to
     * @return HelpReply the created help reply
     */
    public function createHelpReply(string $text, HelpRequest $helpRequest): HelpReply
    {
        $helpReply = new HelpReply($text, new DateTime());
        $helpRequest->setReply($helpReply);
        return $helpReply;
    }

    /**
     * Adds a feedback to the reply of the given help request.
     * @param HelpRequest $helpRequest the help request which reply is added the feedback
     * @param string $feedback the feedback to add to the help reply (at most 300 characters)
     * @return void
     */
    public function addFeedbackToReply(HelpRequest $helpRequest, string $feedback): void
    {
        $helpRequest->getReply()->setFeedback($feedback);
    }
}