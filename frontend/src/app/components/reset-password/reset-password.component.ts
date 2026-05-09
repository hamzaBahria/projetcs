import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { PasswordService } from '../../services/password.service';
import { passwordComplexity } from '../../validators/password.validator';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-reset-password',
  templateUrl: './reset-password.component.html',
  styleUrls: ['./reset-password.component.scss'],
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, NgIf]
})
export class ResetPasswordComponent implements OnInit {
  resetForm: FormGroup;
  token = '';
  loading = false;
  error = '';
  success = '';

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private passwordService: PasswordService
  ) {
    this.resetForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required, Validators.minLength(8), passwordComplexity()]],
      password_confirmation: ['', [Validators.required]]
    }, { validators: this.passwordsMatch });
  }

  ngOnInit(): void {
    this.token = this.route.snapshot.queryParams['token'] || '';
    if (this.route.snapshot.queryParams['email']) {
      this.resetForm.patchValue({ email: this.route.snapshot.queryParams['email'] });
    }
  }

  passwordsMatch(group: FormGroup) {
    const pass = group.get('password')?.value;
    const confirm = group.get('password_confirmation')?.value;
    return pass === confirm ? null : { notMatching: true };
  }

  onSubmit(): void {
    if (this.resetForm.invalid) return;
    this.loading = true;
    this.error = '';

    this.passwordService.resetPassword({
      token: this.token,
      email: this.resetForm.value.email!,
      password: this.resetForm.value.password!,
      password_confirmation: this.resetForm.value.password_confirmation!
    }).subscribe({
      next: (res) => {
        if (res.success) {
          this.success = 'Mot de passe réinitialisé ! Redirection...';
          setTimeout(() => this.router.navigate(['/login']), 2000);
        } else {
          this.error = res.message || 'Erreur';
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || 'Erreur de réinitialisation';
        this.loading = false;
      }
    });
  }
}
