-- --------------------------------------------------------

--
-- RH - Create table to track the last 20 minutes of faptcha attempts (spambot banning.)
--      This standalone file is temporary and needs to be used interactively,
--      ultimately it should be rolled into install scripts.
--

CREATE TABLE `faptcha_attempts` (
  `ip` varchar(20) NOT NULL,
  `timestamp` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

