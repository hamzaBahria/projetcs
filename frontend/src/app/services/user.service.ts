import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { User } from '../models/user.model';

interface UserResponse {
  success: boolean;
  message?: string;
  data?: User;
  user?: User;
  avatar_url?: string;
}

@Injectable({ providedIn: 'root' })
export class UserService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  getProfile(): Observable<UserResponse> {
    return this.http.get<UserResponse>(`${this.apiUrl}/user`);
  }

  updateProfile(data: { name: string; email: string }): Observable<UserResponse> {
    return this.http.put<UserResponse>(`${this.apiUrl}/user/update`, data);
  }

  uploadAvatar(formData: FormData): Observable<UserResponse> {
    return this.http.post<UserResponse>(`${this.apiUrl}/user/avatar`, formData);
  }
}
