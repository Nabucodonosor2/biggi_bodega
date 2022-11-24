---------------------f_get_parametro------------------
--	Esta funcion permite retornar los valores que se encuentran en la tabla 
--	Parametro solo con el codigo del parametro

CREATE FUNCTION [dbo].[f_get_parametro](@ve_cod_parametro numeric)
RETURNS varchar(100)
AS
BEGIN

declare @valor varchar(100)	
	select @valor = valor
	from parametro
	where cod_parametro = @ve_cod_parametro
			
if @valor = '' 
	set @valor  = null

return @valor;

end
go
