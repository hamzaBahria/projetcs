import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { passwordComplexity } from '../../validators/password.validator';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-verify-email',
  templateUrl: './verify-email.component.html',
  styleUrls: ['./verify-email.component.scss'],
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, NgIf]
})
export class VerifyEmailComponent implements OnInit {
  form: FormGroup;
  loading = false;
  error = '';
  success = '';
  verified = false;

  constructor(
    private fb: FormBuilder,
    private route: ActivatedRoute,
    private router: Router,
    private authService: AuthService
  ) {
    this.form = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      code: ['', [Validators.required, Validators.pattern('^[0-9]{6}$')]],
      password: ['', [Validators.required, Validators.minLength(8), passwordComplexity()]],
      password_confirmation: ['', [Validators.required]]
    }, { validators: this.passwordsMatch });
  }

  ngOnInit(): void {
    this.route.queryParams.subscribe(params => {
      if (params['email']) {
        this.form.patchValue({ email: params['email'] });
      }
    });
  }

  passwordsMatch(group: FormGroup) {
    const pass = group.get('password')?.value;
    const confirm = group.get('password_confirmation')?.value;
    return pass === confirm ? null : { notMatching: true };
  }

  onSubmit(): void {
    if (this.form.invalid) return;
    this.loading = true;
    this.error = '';
    this.success = '';

    this.authService.verifyCode(this.form.value).subscribe({
      next: (res) => {
        if (res.success && res.token) {
          this.authService.setUser(res.user!);
          this.authService.setToken(res.token);
          this.verified = true;
          this.success = res.message || 'Email vérifié avec succès.';
          setTimeout(() => this.router.navigate(['/dashboard']), 2000);
        } else if (res.success) {
          this.success = res.message || 'Email déjà vérifié.';
          setTimeout(() => this.router.navigate(['/login']), 2000);
        } else {
          this.error = res.message || 'Erreur de vérification.';
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || 'Erreur de vérification.';
        this.loading = false;
      }
    });
  }
}
