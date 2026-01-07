import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'registrar' | 'guest' | 'superadmin' | 'principal' | 'student';
    status?: string;
    email_verified_at?: string;
    created_at: string;
    updated_at: string;
}

export interface DocumentType {
    id: number;
    name: string;
    category: 'Official' | 'Informal' | 'Certified';
    description?: string | null;
    processing_days?: number;
    is_active?: boolean;
    requests_count?: number;
    created_at: string;
    updated_at: string;
}

export interface DocumentRequest {
    id: number;
    tracking_id: string;
    student_email: string;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    lrn: string;
    grade_level: string;
    section: string | null;
    track_strand: string | null;
    school_year_last_attended: string;
    photo_path: string | null;
    document_type_id: number;
    document_type?: DocumentType;
    purpose: string | null;
    status: 'Pending' | 'Verified' | 'Processing' | 'Ready' | 'Completed' | 'Rejected';
    admin_remarks: string | null;
    otp_code: string | null;
    otp_expires_at: string | null;
    otp_verified: boolean;
    signature: string | null;
    created_at: string;
    updated_at: string;
    request_logs?: RequestLog[];
}

export interface Track {
    id: number;
    category: string;
    code: string;
    name: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface RequestLog {
    id: number;
    document_request_id: number;
    user_id: number | null;
    action: string;
    old_value: string | null;
    new_value: string | null;
    description: string | null;
    user?: User;
    created_at: string;
    updated_at: string;
}

export interface DashboardStats {
    total: number;
    pending: number;
    verified: number;
    processing: number;
    ready: number;
    completed: number;
    rejected: number;
}

export interface TrackStrandGroup {
    [track: string]: {
        [strand: string]: string;
    };
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
};
