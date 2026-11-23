export type ProjectNoteReviewStatus = 'unreviewed' | 'in_review' | 'reviewed';

export interface ProjectNote {
    uuid: string;
    title: string;
    body: string;
    review_status: ProjectNoteReviewStatus;
    pinned_at: string | null;
    created_at: string;
    updated_at: string;
}

export type ProjectNoteDraft = Pick<ProjectNote, 'title' | 'body'>;
export type ProjectNoteChanges = Partial<ProjectNoteDraft & { is_pinned: boolean; review_status: ProjectNoteReviewStatus }>;

export interface ProjectNotesPage {
    data: ProjectNote[];
    links: { next: string | null };
    meta: { current_page: number };
}
