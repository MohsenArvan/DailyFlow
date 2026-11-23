import api from '@/services/api';
import type { ProjectNote, ProjectNoteChanges, ProjectNoteDraft, ProjectNotesPage } from '@/types/projectNote';

function notesPath(projectUuid: string) {
    return `/projects/${projectUuid}/notes`;
}

export async function listProjectNotes(projectUuid: string, page: number, signal?: AbortSignal) {
    const response = await api.get<ProjectNotesPage>(notesPath(projectUuid), { params: { page }, signal });
    return response.data;
}

export async function createProjectNote(projectUuid: string, input: ProjectNoteDraft) {
    const response = await api.post<{ data: ProjectNote }>(notesPath(projectUuid), input);
    return response.data.data;
}

export async function updateProjectNote(projectUuid: string, noteUuid: string, input: ProjectNoteChanges) {
    const response = await api.patch<{ data: ProjectNote }>(`${notesPath(projectUuid)}/${noteUuid}`, input);
    return response.data.data;
}

export async function deleteProjectNote(projectUuid: string, noteUuid: string) {
    await api.delete(`${notesPath(projectUuid)}/${noteUuid}`);
}
