CREATE FUNCTION dbo.f_anos_ventas_x_mes
(
) 
RETURNS @List TABLE (ANO numeric(4),NOM_ANO numeric(4))

BEGIN
DECLARE @ano_actual numeric(4),
		@ano_minimo numeric(4)
		
set @ano_minimo = 2010
set @ano_actual =YEAR(GETDATE())

INSERT INTO @List values (@ano_actual,@ano_actual)

WHILE @ano_minimo <> @ano_actual
BEGIN
 set @ano_actual = @ano_actual -1
 INSERT INTO @List values (@ano_actual,@ano_actual)
END


RETURN
END;
