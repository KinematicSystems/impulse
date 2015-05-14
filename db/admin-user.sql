-- Initial admin password is 'change.me'
REPLACE INTO `user_account` (`id`,`firstName`,`lastName`,`enabled`,`sysadmin`,`sysuser`,
`initials`,`middleName`,`email`,`organization`,`phone`,`poc`,`pocPhone`,`notes`)
VALUES
('admin','Admin','Admin',1,1,0,'SA','Admin','admin@yourserver.co','Systems Administration','999-555-1212','Sys Admin','999-111-2222',NULL);

REPLACE INTO `user_credentials` (`id`,`password`) VALUES
('admin','$2y$10$c5OnMdM4h0zTp0WkwbnLMuMLftM9IKGqcVjkgUzuz.KQznQ89S8pu');
