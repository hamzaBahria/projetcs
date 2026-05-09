import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

interface PasswordResponse {
  success: boolean;
  message?: string;
}

@Injectable({ providedIn: 'root' })
export class PasswordService {
  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) {}

  forgotPassword(email: string): Observable<PasswordResponse> {
    return this.http.post<PasswordResponse>(`${this.apiUrl}/password/forgot`, { email });
  }

  resetPassword(data: { token: string; email: string; password: string; password_confirmation: string }): Observable<PasswordResponse> {
    return this.http.post<PasswordResponse>(`${this.apiUrl}/password/reset`, data);
  }

  changePassword(data: { current_password: string; new_password: string; new_password_confirmation: string }): Observable<PasswordResponse> {
    return this.http.put<PasswordResponse>(`${this.apiUrl}/password/change`, data);
  }

  setPassword(data: { email: string; password: string; password_confirmation: string }): Observable<PasswordResponse> {
    return this.http.post<PasswordResponse>(`${this.apiUrl}/auth/google/set-password`, data);
  }
}
