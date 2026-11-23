<?php

namespace App\Enums;

enum ProjectNoteReviewStatus: string
{
    case Unreviewed = 'unreviewed';
    case InReview = 'in_review';
    case Reviewed = 'reviewed';
}
