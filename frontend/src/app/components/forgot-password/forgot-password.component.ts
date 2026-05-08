import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { PasswordService } from '../../services/password.service';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-forgot-password',
  templateUrl: './forgot-password.component.html',
  styleUrls: ['./forgot-password.component.scss'],
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, NgIf]
})
export class ForgotPasswordComponent {
  emailForm: FormGroup;
  loading = false;
  success = '';
  error = '';

  constructor(
    private fb: FormBuilder,
    private passwordService: PasswordService
  ) {
    this.emailForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]]
    });
  }

  onSubmit(): void {
    if (this.emailForm.invalid) return;
    this.loading = true;
    this.error = '';
    this.success = '';

    this.passwordService.forgotPassword(this.emailForm.value.email!).subscribe({
      next: (res) => {
        this.success = res.message || 'Email envoyé !';
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || 'Email non trouvé';
        this.loading = false;
      }
    });
  }
}
