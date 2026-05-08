import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { AuthService } from '../../services/auth.service';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss'],
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, NgIf]
})
export class LoginComponent implements OnInit {
  loginForm: FormGroup;
  error = '';
  loading = false;

  constructor(
    private fb: FormBuilder,
    private authService: AuthService,
    private router: Router,
    private route: ActivatedRoute
  ) {
    this.loginForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      password: ['', [Validators.required]]
    });
  }

  ngOnInit(): void {
    const token = this.route.snapshot.queryParams['token'];
    const user = this.route.snapshot.queryParams['user'];
    if (token && user) {
      this.authService.handleGoogleCallback(token, user);
      this.router.navigate(['/dashboard']);
    }
  }

  onSubmit(): void {
    if (this.loginForm.invalid) return;
    this.loading = true;
    this.error = '';

    this.authService.login(this.loginForm.value as { email: string; password: string }).subscribe({
      next: (res) => {
        if (res.success) {
          this.router.navigate(['/dashboard']);
        } else {
          this.error = res.message || 'Erreur de connexion';
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || 'Erreur de connexion';
        this.loading = false;
      }
    });
  }

  loginGoogle(): void {
    window.location.href = 'http://localhost:8000/api/auth/google';
  }
}
