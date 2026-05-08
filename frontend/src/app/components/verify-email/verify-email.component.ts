import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-verify-email',
  templateUrl: './verify-email.component.html',
  styleUrls: ['./verify-email.component.scss'],
  standalone: true,
  imports: [RouterLink, NgIf]
})
export class VerifyEmailComponent implements OnInit {
  status: string | null = null;
  message: string | null = null;

  constructor(private route: ActivatedRoute) {}

  ngOnInit(): void {
    this.status = this.route.snapshot.queryParams['status'];
    this.message = this.route.snapshot.queryParams['message'] || null;
  }
}
