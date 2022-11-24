------------------  spu_opciones_especiales  --------------------------
CREATE PROCEDURE [dbo].[spu_opciones_especiales](@ve_cod_perfil numeric, @ve_cod_item_menu varchar(10), @ve_autoriza varchar(1))
AS
BEGIN

	DECLARE @vl_count numeric,
			@vl_AUTORIZA_MENU varchar(1)

	if		@ve_autoriza		= 'S' 
	begin
		set	@vl_AUTORIZA_MENU	= 'E'
	end
	else
	begin
		set @vl_AUTORIZA_MENU	= 'N'
	end

select	@vl_count =count(*) 
from	AUTORIZA_MENU
where	cod_perfil		= @ve_cod_perfil and
		cod_item_menu	= @ve_cod_item_menu;

if		@vl_count = 0
begin 
	insert into 
				AUTORIZA_MENU (cod_perfil, cod_item_menu, autoriza_menu, impresion)
	values		(@ve_cod_perfil, @ve_cod_item_menu, @vl_AUTORIZA_MENU,'N')
END
else
	BEGIN
		update	AUTORIZA_MENU
		set		AUTORIZA_MENU = @vl_AUTORIZA_MENU				
		where	cod_perfil = @ve_cod_perfil and
				cod_item_menu = @ve_cod_item_menu
	end
END
go