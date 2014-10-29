-- Initial admin password is 'change.me'
INSERT INTO `user_account`
(`id`,
`firstName`,
`lastName`,
`password`,
`enabled`,
`sysadmin`,
`email`)
VALUES
("admin",
 "Admin",
 "SysAdmin",
 "$2y$10$c5OnMdM4h0zTp0WkwbnLMuMLftM9IKGqcVjkgUzuz.KQznQ89S8pu",
 1,
 1,
 "admin@yourserver.com");
