import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterLink, ActivatedRoute, Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { PasswordService } from '../../services/password.service';
import { passwordComplexity } from '../../validators/password.validator';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-set-password',
  templateUrl: './set-password.component.html',
  styleUrls: ['./set-password.component.scss'],
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, NgIf]
})
export class SetPasswordComponent implements OnInit {
  form: FormGroup;
  email = '';
  error = '';
  success = false;
  loading = false;

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private authService: AuthService,
    private passwordService: PasswordService
  ) {
    this.form = this.fb.group({
      password: ['', [Validators.required, Validators.minLength(8), passwordComplexity()]],
      password_confirmation: ['', [Validators.required]]
    }, { validators: this.passwordsMatch });
  }

  ngOnInit(): void {
    this.route.queryParams.subscribe(params => {
      this.email = params['email'] || '';
      if (!this.email) {
        this.router.navigate(['/login']);
      }
    });
  }

  passwordsMatch(group: FormGroup) {
    const pass = group.get('password')?.value;
    const confirm = group.get('password_confirmation')?.value;
    return pass === confirm ? null : { notMatching: true };
  }

  onSubmit(): void {
    if (this.form.invalid || !this.email) return;
    this.loading = true;
    this.error = '';

    this.passwordService.setPassword({
      email: this.email,
      password: this.form.value.password,
      password_confirmation: this.form.value.password_confirmation,
    }).subscribe({
      next: (res: any) => {
        if (res.success && res.token) {
          this.authService.setToken(res.token);
          this.authService.setUser(res.user);
          this.router.navigate(['/dashboard']);
        } else {
          this.error = res.message || 'Une erreur est survenue.';
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || err.error?.errors?.password?.[0] || 'Une erreur est survenue.';
        this.loading = false;
      }
    });
  }
}
